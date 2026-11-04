// Local test harness for the bestefar-audio player (NOT part of the site).
//   node tests/support/dev-server.js [port]
// - serves httpd.www/ statically
// - rewrites the hard-coded production API base in data/script.js to this server
// - implements the quiz endpoints the generator/player use, in memory, with the
//   same response shapes as the real backend (nothing touches production)
// - serves a generated 30s tone at /test-audio/tone.wav for playback tests
const http = require("http");
const fs = require("fs");
const path = require("path");

const port = Number(process.argv[2] || 8890);
const root = path.join(__dirname, "..", "..", "httpd.www");
const quizzes = new Map(); // id -> { id, quizName, quizForms: [] }
let nextId = 1;
const types = { ".html": "text/html", ".js": "text/javascript", ".css": "text/css", ".json": "application/json", ".png": "image/png", ".jpg": "image/jpeg", ".jpeg": "image/jpeg", ".ttf": "font/ttf", ".TTF": "font/ttf" };

function wav(seconds = 30, rate = 8000) {
  const n = seconds * rate, buf = Buffer.alloc(44 + n);
  buf.write("RIFF", 0); buf.writeUInt32LE(36 + n, 4); buf.write("WAVEfmt ", 8);
  buf.writeUInt32LE(16, 16); buf.writeUInt16LE(1, 20); buf.writeUInt16LE(1, 22);
  buf.writeUInt32LE(rate, 24); buf.writeUInt32LE(rate, 28); buf.writeUInt16LE(1, 32); buf.writeUInt16LE(8, 34);
  buf.write("data", 36); buf.writeUInt32LE(n, 40);
  for (let i = 0; i < n; i++) buf[44 + i] = 128 + Math.round(40 * Math.sin((2 * Math.PI * 440 * i) / rate));
  return buf;
}
const tone = wav();

const readBody = (req) => new Promise((resolve) => {
  let d = "";
  req.on("data", (c) => (d += c));
  req.on("end", () => { try { resolve(JSON.parse(d || "{}")); } catch { resolve({}); } });
});
const send = (res, code, obj) => {
  res.writeHead(code, { "content-type": "application/json", "access-control-allow-origin": "*", "cache-control": "no-store" });
  res.end(JSON.stringify(obj));
};

http.createServer(async (req, res) => {
  const url = new URL(req.url, "http://x");
  const p = decodeURIComponent(url.pathname);
  if (req.method === "OPTIONS") {
    res.writeHead(204, { "access-control-allow-origin": "*", "access-control-allow-methods": "*", "access-control-allow-headers": "*" });
    return res.end();
  }
  if (p === "/test-audio/tone.wav") {
    res.writeHead(200, { "content-type": "audio/wav", "content-length": tone.length });
    return res.end(tone);
  }

  // ── quiz API (same shapes as the real backend) ──
  if (p === "/api/forms/save-form" && req.method === "POST") {
    const b = await readBody(req);
    if (!b.page || !b.quizName) return send(res, 400, { error: "Page and quizName are required." });
    let q = [...quizzes.values()].find((x) => x.quizName === b.quizName);
    if (!q) quizzes.set((q = { id: "t" + nextId++, quizName: b.quizName, quizForms: [] }).id, q);
    const i = q.quizForms.findIndex((f) => f.page === b.page);
    const form = { ...b }; delete form.quizName;
    if (i >= 0) q.quizForms[i] = form; else q.quizForms.push(form);
    return send(res, 200, { success: true, showId: q.id });
  }
  let m = p.match(/^\/api\/forms\/update-show\/([^/]+)$/);
  if (m && req.method === "PUT") {
    const q = quizzes.get(m[1]);
    const b = await readBody(req);
    if (!q) return send(res, 404, { success: false, message: "not found" });
    if (!b.quizName || !Array.isArray(b.quizForms)) return send(res, 400, { success: false, message: "Invalid payload" });
    q.quizName = b.quizName; q.quizForms = b.quizForms;
    return send(res, 200, { success: true });
  }
  m = p.match(/^\/api\/audio-quiz\/get-audio-quiz\/([^/]+)$/);
  if (m && req.method === "GET") {
    const q = quizzes.get(m[1]);
    return q ? send(res, 200, { id: q.id, quizName: q.quizName, quizForms: [...q.quizForms].sort((a, b) => a.page - b.page) }) : send(res, 404, { error: "not found" });
  }
  if (p === "/api/_debug/quizzes") return send(res, 200, [...quizzes.values()]);

  // ── static files ──
  let file = path.join(root, p === "/" ? "index.html" : p);
  if (!file.startsWith(root)) { res.writeHead(403); return res.end(); }
  fs.readFile(file, (err, data) => {
    if (err) { res.writeHead(404); return res.end("404"); }
    if (file.endsWith(path.join("bestefar-audio", "data", "script.js"))) {
      data = Buffer.from(data.toString().replace("https://api.bestefar.no/api/audio-quiz", `http://localhost:${port}/api/audio-quiz`));
    }
    res.writeHead(200, { "content-type": types[path.extname(file)] || "application/octet-stream", "cache-control": "no-store" });
    res.end(data);
  });
}).listen(port, () => console.log("dev server on http://localhost:" + port));
