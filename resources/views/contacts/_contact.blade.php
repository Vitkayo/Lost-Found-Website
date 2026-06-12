<tr>
    <td class="text-center">{{ $value['id'] }}</td>
    <td>{{ $value['name'] }}</td>
    <td>{{ $value['gender'] }}</td>
    <td>{{ $value['email'] }}</td>
    <td>{{ $value['phone'] }}</td>
    <td>{{ $value['company'] }}</td>
    <td class="text-center">
        <a href="{{ route('contacts.show', $value['id']) }}"
           class="btn btn-sm btn-circle btn-outline-info" title="Show"><i class="bi bi-eye-fill"></i></a>
        <a href="{{ route('contacts.edit', $value['id']) }}" class="btn btn-sm btn-circle btn-outline-secondary"
           title="Edit"><i class="bi bi-pencil-square"></i></a>
        <form action="{{ route('contacts.destroy', $value['id']) }}" method="POST" style="display:inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-circle btn-outline-danger" onclick="return confirm('Are you sure?')">
                <i class="bi bi-x-circle"></i>
            </button>
        </form>
    </td>
</tr>
