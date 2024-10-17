<!DOCTYPE html>
<html>
<head>
<title>
Daily Report
</title>
</head>
@foreach ($tasks as $task)
    <p>{{ $task->title}}</p>
@endforeach

</body>
</html>
