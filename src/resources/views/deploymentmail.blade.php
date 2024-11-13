<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deployment Success Report</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333;
            padding-top: 20px;
            padding-bottom: 20px;
        }
        table {
            width: 100%;
            border-spacing: 0;
            border: none;
        }
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            background-color: #ffffff;
            border: 1px solid #ecf0f1; /* Adding explicit border to container */
            border-radius: 10px;
            overflow: hidden;
        }
        .header, .footer {
            width: 100%;
            padding: 20px 0;
            text-align: center;
        }
        .header {
            background-color: #17d86e;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-top: 1px solid #ecf0f1;
            border-bottom: 1px solid #ecf0f1;
        }
        .failed .header {
            background-color: tomato;
        }
        .footer {
            font-size: 18px;
            padding: 8px;
            background-color: #17d86e;
            color: #ffffff;
            border-top: 1px solid #ecf0f1; /* Border for footer */
        }
        .failed .footer {
            background-color: tomato;
        }
        .content {
            padding: 20px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section h2 {
            font-size: 20px;
            color: #2c3e50;
            border-bottom: 2px solid #ecf0f1;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .section pre {
            background-color: #061414cc;
            padding: 15px;
            border-radius: 5px;
            font-size: 14px;
            line-height: 1.5;
            color: white;
            white-space: pre-wrap;
            border: 1px solid #ddd; /* Added border to pre tags */
        }
        .info {
            color: #3498db;
        }
        .done {
            color: #2ecc71;
        }
        .highlight {
            font-weight: bold;
        }
        @media only screen and (max-width: 600px) {
            .content {
                padding: 15px;
            }
            .section h2 {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>
    <table class="container">
        <tr class="header {{$deploymentStatus}}">
            <td>
                <h1 style="margin: 0; padding:15px;">
                    @if($deploymentStatus == "success")
                        🎉 Deployment Success Report on {{ $env_pointing }} 🎉
                    @else
                        🚨 Deployment Failure Report on {{$env_pointing}} 🚨
                    @endif
                </h1>
            </td>
        </tr>

        <tr>
            <td class="content" style="padding: 20px;">
                @foreach ($result as $key => $value)
                    <div class="section" style="margin-bottom: 20px;">
                        <h2 style="font-size: 20px; color: #2c3e50; border-bottom: 2px solid #ecf0f1; padding-bottom: 5px; margin-bottom: 15px;">{{ ucwords(str_replace("_", " ", $key)) }}</h2>
                    </div>
                    @if(is_array($value))
                        @if((isset($value["skipped"]) && $value["skipped"] == false) || isset($value['failed']))
                            @if($value["stdout"] != "")
                                <pre style="background-color: #061414cc; padding: 15px; border-radius: 5px; font-size: 14px; line-height: 1.5; color: white; white-space: pre-wrap; border: 1px solid #ddd;">@if($value["stderr"] != ""){{$value["stderr"]}}<br><br>@endif{{$value  ["stdout"]}}</pre>
                            @else
                                <pre style="background-color: #061414cc; padding: 15px; border-radius: 5px; font-size: 14px; line-height: 1.5; color: white; white-space: pre-wrap; border: 1px solid #ddd;">No Output</pre>
                            @endif
                        @else
                            <pre style="background-color: #ecf0f1; padding: 15px; border-radius: 5px; font-size: 14px; line-height: 1.5; color: #2c3e50; white-space: pre-wrap; border: 1px solid #ddd;">This step is Skipped</pre>
                            <pre style="background-color: #ecf0f1; padding: 15px; border-radius: 5px; font-size: 14px; line-height: 1.5; color: #2c3e50; white-space: pre-wrap; border: 1px solid #ddd;"><b>Reason:</b></pre>
                            <ul style="font-family:monospace;">
                                <li>{{$value['skip_reason']}}</li>
                                <li>{{$value['false_condition']}}</li>
                            </ul>
                        @endif
                    @else
                        <pre style="background-color: #061414cc; padding: 15px; border-radius: 5px; font-size: 14px; line-height: 1.5; color: white; white-space: pre-wrap; border: 1px solid #ddd;">{{ucwords($value)}}</pre>
                    @endif
                @endforeach
            </td>
        </tr>

        <tr class="footer {{$deploymentStatus}}">
            <td>
                <p>&copy; 2024 Deployment Team | Mohd Ishrat</p>
            </td>
        </tr>
    </table>
</body>
</html>
