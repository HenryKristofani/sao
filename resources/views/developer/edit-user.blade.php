<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-light">

    <div class="card shadow p-4" style="width: 400px;">
        <h3 class="text-center">Edit User</h3>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('developer.updateUser', $user->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label class="form-label">Nama</label>
        <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Role</label>
        <select name="role" class="form-control" required>
            <option value="kantor" {{ $user->role == 'kantor' ? 'selected' : '' }}>Kantor</option>
            <option value="pabrik" {{ $user->role == 'pabrik' ? 'selected' : '' }}>Pabrik</option>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Password Baru (opsional)</label>
        <input type="password" name="password" class="form-control">
    </div>

    <button type="submit" class="btn btn-primary w-100">Simpan Perubahan</button>
    <a href="{{ route('developer.dashboard') }}" class="btn btn-secondary w-100 mt-2">Batal</a>
</form>

    </div>

</body>
</html>
