<x-layout.app :title="'Email Verification'" :mainClass="'text-center'">
    <div class="p-5 mt-10 mb-20 inline-block rounded-md border border-solid {{ $success ? 'text-green-400' : 'text-red-600' }}">
        <h1>{{ $message }}</h1>
        @if ($success)
            <p class="">Your email is now verified. You will receive notifications on price change to all your advert URLs added under this email.</p>
            <p class="">Any time you can add more URLs to subscribe on price changes</p>
        @else
            <p class="">Please try again or contact support if the issue persists.</p>
        @endif
        <div class="z-10 text-right">
            <a href="{{ route('home') }}" class="inline-block mt-4 px-6 py-2 bg-blue-600 text-white font-medium text-lg rounded-lg hover:bg-blue-700 transition ">Subscribe for New URLs</a>
        </div>
    </div>
</x-layout.app>
