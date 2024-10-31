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
            transform: translateY(-20px); /* Start slightly above */
            opacity: 0; /* Start invisible */
            pointer-events: none; /* Prevent interactions when hidden */
        }

        .modal.show {
            transform: translateY(0); /* Slide down to original position */
            opacity: 1; /* Fully visible */
            pointer-events: auto; /* Allow interactions when shown */
        }

        /* Modal backdrop visibility transition */
        .modal-backdrop {
            transition: opacity 0.5s ease;
            opacity: 0; /* Start invisible */
        }

        .modal-backdrop.show {
            opacity: 1; /* Fully visible */
        }
    </style>
</head>

<body class="pt-4 w-screen h-screen bg-gray-200">
    <div class="">
        @yield('content')
    </div>
</body>

</html>