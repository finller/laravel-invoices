<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

    <div class="flex p-16">

        <div class="relative mx-auto flex aspect-[210/297] w-full max-w-2xl flex-col bg-white p-12 shadow-md">

            @include('invoices::default.invoice', [
                'invoice' => $invoice,
            ])

        </div>

    </div>

</body>

</html>
