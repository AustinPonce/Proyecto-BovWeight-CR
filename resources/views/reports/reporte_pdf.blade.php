<!DOCTYPE html>
<html>
<head>

    <meta charset="utf-8">

    <title>Reporte BovWeight-CR</title>

    <style>

        body{
            font-family: Arial, sans-serif;
        }

        table{
            width:100%;
            border-collapse: collapse;
            margin-top:20px;
        }

        th, td{
            border:1px solid black;
            padding:8px;
            text-align:left;
        }

        th{
            background:#f2f2f2;
        }

    </style>

</head>

<body>

    <h1>Reporte de Pesaje</h1>

    <table>

        <thead>

            <tr>
                <th>Animal</th>
                <th>Peso</th>
                <th>Fecha</th>
            </tr>

        </thead>

        <tbody>

            @foreach($datos as $dato)

                <tr>

                    <td>{{ $dato['animal'] }}</td>

                    <td>{{ $dato['peso'] }} kg</td>

                    <td>{{ $dato['fecha'] }}</td>

                </tr>

            @endforeach

        </tbody>

    </table>

</body>
</html>