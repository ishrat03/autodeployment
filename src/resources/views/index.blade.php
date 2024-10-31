@extends('autodeployment::layout.header')
@section('content')
<div class="w-full flex justify-between items-center mb-3 mt-1 pl-5 pr-5">
    <div>
        <h3 class="text-lg font-semibold text-slate-800">All Deployment List</h3>
        <p class="text-slate-500">Overview of the deployments.</p>
    </div>
    <div class="ml-3">
        <div class="w-full max-w-sm min-w-[200px] relative">
            <div class="relative">
                <input
                    class="bg-white w-full pr-11 h-10 pl-3 py-2 bg-transparent placeholder:text-slate-400 text-slate-700 text-sm border border-slate-200 rounded transition duration-300 ease focus:outline-none focus:border-slate-400 hover:border-slate-400 shadow-sm focus:shadow-md"
                    placeholder="Search for deployments..." />
                <button class="absolute h-8 w-8 right-1 top-1 my-auto px-2 flex items-center bg-white rounded "
                    type="button">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3"
                        stroke="currentColor" class="w-8 h-8 text-slate-600">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
<div class="alert alert-success" id="success">
</div>
<div class="alert alert-danger" id="danger">
</div>

<div
    class="relative flex flex-col w-full h-full overflow-scroll text-gray-700 bg-white shadow-md rounded-lg bg-clip-border pr-3 pl-3 pt-2">
    <table class="w-full text-left table-auto min-w-max">
        <thead>
            <tr>
                @foreach ($headers as $header)
                    <th
                        class="p-4 border-b border-slate-300 bg-slate-50 {{$header == 'Deployment ID' ? '' : 'text-center'}}">
                        <p class="block text-sm font-normal leading-none text-slate-500">
                            {{$header}}
                        </p>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div id="myModal"
    class="fixed inset-0 z-50 hidden bg-gray-800 bg-opacity-50 pl-5 pr-5 flex items-center justify-center modal-backdrop">
    <div class="bg-white rounded-lg shadow-lg w-full p-5 modal">
        <h2 class="text-lg font-semibold mb-4 text-center text-gray-500">Deployment Status</h2>
        <hr />
        <div id="resultBox">

        </div>
        <div class="flex justify-center space-x-4">
            <button id="closeModal" class="bg-red-500 text-white font-bold py-2 px-4 rounded">
                Close
            </button>
        </div>
    </div>
</div>
<script>
    const createTableRow = (value) => {
        const row = $(`
                <tr class="hover:bg-slate-50">
                    <td class="p-2 border-b border-slate-200 py-2">
                        <p class="block font-semibold text-sm text-slate-800 pl-5">${value.id}</p>
                    </td>
                    <td class="p-2 border-b border-slate-200 py-2 text-center">
                        <p class="block text-sm text-slate-800">${value.name}</p>
                    </td>
                    <td class="p-2 border-b border-slate-200 py-2 text-center">
                        <p class="text-sm text-slate-500">${value.status}</p>
                    </td>
                    <td class="p-2 border-b border-slate-200 py-2 text-center">
                        <p class="text-sm text-slate-500">${value.webhook_time}</p>
                    </td>
                    <td class="p-2 border-b border-slate-200 py-2 text-center">
                        <p class="text-sm text-slate-500">${value.source_branch_name}</p>
                    </td>
                    <td class="p-2 border-b border-slate-200 py-2 text-center">
                        <p class="text-sm text-slate-500">${value.destination_branch_name}</p>
                    </td>
                    <td class="p-2 border-b border-slate-200 py-2 text-center">
                        <p class="text-sm text-slate-500">${value.deployment_start_time}</p>
                    </td>
                    <td class="p-2 border-b border-slate-200 py-2 text-center">
                        <p class="text-sm text-slate-500">${value.deployment_end_time}</p>
                    </td>
                    <td class="p-2 border-b border-slate-200 py-2 text-center">
                        <p class="text-sm text-slate-500">${value.timediff}</p>
                    </td>
                    <td class="p-2 border-b border-slate-200 py-2 text-center">
                        <div class="flex justify-center space-x-0">
                            ${value.status === "Processing" ? `<button onclick="viewDeployments(${value.id})" class="px-4 py-1 bg-green-500 text-white font-semibold rounded-lg hover:bg-green-600 focus:outline-none hover:scale-105"><i class="fa-solid fa-eye"></i></button>`: `${value.status === 'Pending'
                                ? `<button onclick="(reloadAndStartDeployment(${value.id}))" class="px-4 py-1 bg-green-500 text-white font-semibold rounded-l-lg hover:bg-green-600 focus:outline-none hover:scale-105">
                                                    <i class="fa-solid fa-play"></i>
                                                </button>`
                                : `<button onclick="(reloadAndStartDeployment(${value.id}))" class="px-4 py-1 bg-purple-500 text-white font-semibold rounded-l-lg hover:bg-purple-600 focus:outline-none hover:scale-105">
                                                    <i class="fa-solid fa-rotate-right"></i>
                                                </button>`
                            }
                            <a href="/" class="px-4 py-1 bg-red-500 text-white font-semibold rounded-r-lg hover:bg-red-700 focus:outline-none hover:scale-105">
                                <i class="fa-solid fa-trash-can"></i>
                            </a>`}
                        </div>
                    </td>
                </tr>
            `);
        return row;
    };

    const createNoDeploymentAndLoadingRow = (text) => {
        const row = $(`
                <tr class="hover:bg-slate-50">
                    <td class="p-2 border-b border-slate-200 py-2 text-center" colspan="10">
                        <p class="block font-semibold text-xl text-slate-800 pl-5">${text}</p>
                    </td>
                </tr>
            `);
        return row;
    };

    var interval = "";

    $(document).ready(() =>
    {
        loadDeployments();
        // Close modal
        $('#closeModal').on('click', function () {
            $('.modal').removeClass('show'); // Hide the modal content
            setTimeout(() => {
                $('.modal-backdrop').removeClass('show'); // Fade out the backdrop
            }, 500); // Match with modal animation duration
            setTimeout(() => {
                $('#myModal').addClass('hidden'); // Hide the modal backdrop after animation
            }, 510); // Slightly longer to ensure the modal is hidden after fade-out
        });

        // Close modal when clicking outside of the modal content
        $(window).on('click', function (event) {
            if ($(event.target).is('#myModal')) {
                $('.modal').removeClass('show'); // Hide the modal content
                setTimeout(() => {
                    $('.modal-backdrop').removeClass('show'); // Fade out the backdrop
                }, 500); // Match with modal animation duration
                setTimeout(() => {
                    $('#myModal').addClass('hidden'); // Hide the modal backdrop after animation
                }, 510); // Slightly longer to ensure the modal is hidden after fade-out
            }
        });
    });

    function loadDeployments()
    {
        if(interval == "")
        {
            $('tbody').html(createNoDeploymentAndLoadingRow("Fetching data........."));
        }
        $.ajax(
        {
            url: "/deploymentdata",
            method: "GET",
            success: (response) => {
                if (response.header.code === 200)
                {
                    if (response.body.length > 0)
                    {
                        $('tbody').html("");
                        var clearinterval = true;
                        response.body.forEach((data) =>
                        {
                            if(data.status != "Completed")
                            {
                                clearinterval = false;
                            }
                            $('tbody').append(createTableRow(data));
                        })

                        if(clearinterval)
                        {
                            clearInterval(interval);
                        }
                    }
                    else {
                        $('tbody').html(createNoDeploymentAndLoadingRow("No Deployment Found"));
                    }
                }
                else
                {
                    $('tbody').html(createNoDeploymentAndLoadingRow("No Deployment Found"));
                }
            },
            error: () =>
            {
                $("#danger").html("Something went wrong");
            }
        });
    }

    function reloadAndStartDeployment(id)
    {
        Swal.fire(
        {
            title: "Deployment Password",
            input: "text",
            inputAttributes: {
                autocapitalize: "off"
            },
            showCancelButton: true,
            confirmButtonText: "Start Deployment",
            showLoaderOnConfirm: true,
            preConfirm: async (password) =>
            {
                try
                {
                    const url = `/startdeployment/${id}?password=${btoa(password)}`;
                    const response = await fetch(url);
                    if (!response.ok)
                    {
                        return Swal.showValidationMessage(`${JSON.stringify(await response.json())}`);
                    }
                    return response.json();
                }
                catch (error)
                {
                    Swal.showValidationMessage(`Request failed: ${error}`);
                }
            },
            allowOutsideClick: () => !Swal.isLoading()
            }
        ).then((result) =>
        {
            if (result.isConfirmed)
            {
                Swal.fire({
                    title: `${result.value.header.msg}`,
                    icon:result.value.header.status
                });
                loadDeployments();
                setIntervalToReloadData();
            }
        });
    }

    function setIntervalToReloadData()
    {
        interval = setInterval(() =>
        {
            loadDeployments();
        }, 5000);
    }

    function openModal()
    {
        $('#myModal').removeClass('hidden');
        $('.modal-backdrop').addClass('show');
        setTimeout(() =>
        {
            $('.modal').addClass('show');
        }, 10);
    }

    function viewDeployments(id)
    {
        openModal();
        $("#resultBox").html("processing results");
    }
</script>
@endsection