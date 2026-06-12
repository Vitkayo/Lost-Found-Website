<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab 4 Part 1 - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center min-h-screen justify-content-center p-4">
    <div class="text-center bg-white p-5 rounded shadow-sm max-width-md border">
        <h1 class="display-6 fw-bold text-primary mb-2">Royal University of Phnom Penh</h1>
        <p class="text-muted fs-5">Class: ITE | Name: Vath Bundavit</p>
        <hr class="my-4">
        <a href="{{ route('contacts.index') }}" class="btn btn-info px-4 py-2 text-white font-medium">
            Launch Contacts Directory
        </a>
    </div>
</body>
</html>