<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <h1>Login</h1>
    <form action="{{ route('login') }}" method="POST">
        @csrf

        <label for="email">Email: </label>
        <input type="text" name="email" id="email">

        <label for="password">Password: </label>
        <input type="password" name="password" id="password">

        <button type="submit">Login</button>

        {{-- Display error if there is any --}}
        @if ($errors->any())
            <div>
                {{ $errors->first() }}
            </div>
        @endif
    </form>
</body>
</html>