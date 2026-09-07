<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Information</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h2>User Information</h2>
    <table>
        <tr>
            <th>Field</th>
            <th>Value</th>
        </tr>
        <tr>
            <td>Institute</td>
            <td><?php echo $institute; ?></td>
        </tr>
        <tr>
            <td>Address</td>
            <td><?php echo $address; ?></td>
        </tr>
        <tr>
            <td>Postal Code 1</td>
            <td><?php echo $postal1; ?></td>
        </tr>
        <tr>
            <td>Postal Code 2</td>
            <td><?php echo $postal2; ?></td>
        </tr>
        <tr>
            <td>Website</td>
            <td><?php echo $web; ?></td>
        </tr>
        <tr>
            <td>Projector</td>
            <td><?php echo $projector; ?></td>
        </tr>
        <tr>
            <td>First Name</td>
            <td><?php echo $fname; ?></td>
        </tr>
        <tr>
            <td>Last Name</td>
            <td><?php echo $lname; ?></td>
        </tr>
        <tr>
            <td>Phone</td>
            <td><?php echo $phone; ?></td>
        </tr>
        <tr>
            <td>Mobile</td>
            <td><?php echo $mobile; ?></td>
        </tr>
        <tr>
            <td>Email</td>
            <td><?php echo $email; ?></td>
        </tr>
        <tr>
            <td>Password</td>
            <td><?php echo $password; ?></td>
        </tr>
        <tr>
            <td>Status</td>
            <td><?php echo $status; ?></td>
        </tr>
        <tr>
            <td>Date</td>
            <td><?php echo $date; ?></td>
        </tr>
    </table>
</body>
</html>
