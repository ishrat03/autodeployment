@foreach ($result as $key => $value)
    <div class="flex items-center justify-center mb-4 w-full px-5">
        <div class="w-full max-w-screen-lg rounded-2xl bg-gray-200 shadow-neumorphism p-6 mx-auto">
            <h2 class="text-gray-700 text-xl font-semibold mb-4">{{ ucwords(str_replace("_", " ", $key)) }}</h2>
            <hr class="w-full border-0 h-[1px] bg-purple-500">
            <p class="text-gray-500 text-sm width:100%">
                @if(is_array($value))
                    <pre class="content-pre">{{$value["stderr"]}}</pre>
                    <pre class="content-pre">{{$value["stdout"]}}</pre>
                @else
                    <pre class="content-pre">{{ucwords($value)}}</pre>
                @endif
            </p>
        </div>
    </div>
@endforeach