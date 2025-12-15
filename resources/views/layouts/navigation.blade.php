<nav class="bg-white shadow p-4">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        <a href="{{ url('/') }}" class="text-xl font-bold text-blue-600">
            FinTrack
        </a>

        <div class="space-x-4">
            @auth
                <span class="text-gray-700">Hello, {{ Auth::user()->name }}</span>
                <a href="{{ route('profile.edit') }}" class="text-gray-700 hover:text-blue-500">Profile</a>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-red-500 hover:text-red-700">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="text-gray-700 hover:text-blue-500">Login</a>
                <a href="{{ route('register') }}" class="text-gray-700 hover:text-blue-500">Register</a>
            @endauth
        </div>
    </div>
</nav>
