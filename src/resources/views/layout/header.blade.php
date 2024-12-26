<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autodeployment Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/68a436787d.js" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .modal {
            transition: opacity 0.5s ease, transform 0.5s ease;
            transform: translateY(-20px);
            /* Start slightly above */
            opacity: 0;
            /* Start invisible */
            pointer-events: none;
            /* Prevent interactions when hidden */
        }

        .modal.show {
            transform: translateY(0);
            /* Slide down to original position */
            opacity: 1;
            /* Fully visible */
            pointer-events: auto;
            /* Allow interactions when shown */
        }

        /* Modal backdrop visibility transition */
        .modal-backdrop {
            transition: opacity 0.5s ease;
            opacity: 0;
            /* Start invisible */
        }

        .modal-backdrop.show {
            opacity: 1;
            /* Fully visible */
        }

        /* Neumorphism effect for main card */
        /* Existing Neumorphism styling */
        .shadow-neumorphism {
            box-shadow: 8px 8px 16px #bebebe, -8px -8px 16px #ffffff;
        }

        .shadow-neumorphism-button {
            box-shadow: 4px 4px 8px #bebebe, -4px -4px 8px #ffffff;
        }

        /* New styles for <pre> content */
        .content-pre {
            white-space: pre-wrap;
            /* Enable text wrapping */
            overflow-wrap: break-word;
            /* Break long words if needed */
            overflow: auto;
            /* Add scroll for large content */
            /* background-color: #f7f7f7; */
            /* Light background for contrast */
            padding: 0.5rem;
            /* Add some padding */
            border-radius: 8px;
            /* Rounded corners */
            font-family: monospace;
            /* Monospace font for <pre> */
        }

        /* General button styling */
        .btn {
            display: inline-block;
            font-weight: 400;
            text-align: center;
            vertical-align: middle;
            cursor: pointer;
            border: 1px solid transparent;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 0.25rem;
            transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        /* Success button */
        .btn-success {
            color: #fff;
            background-color: #28a745;
            border-color: #28a745;
        }

        .btn-success:hover {
            color: #fff;
            background-color: #218838;
            border-color: #1e7e34;
        }

        /* Danger button */
        .btn-danger {
            color: #fff;
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .btn-danger:hover {
            color: #fff;
            background-color: #c82333;
            border-color: #bd2130;
        }

        .swal2-actions .btn {
            margin: 0 0.5rem;
            /* Adjust the margin as needed */
        }
    </style>
</head>

<body class="pt-4 w-screen h-screen bg-gray-200">
    <div class="">
        @yield('content')
    </div>
</body>

</html>