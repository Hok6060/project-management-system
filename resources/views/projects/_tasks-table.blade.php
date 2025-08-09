<div class="relative overflow-x-auto shadow-md sm:rounded-lg">
    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                <th scope="col" class="px-6 py-3">Task Name</th>
                <th scope="col" class="px-6 py-3">Status</th>
                <th scope="col" class="px-6 py-3">Priority</th>
                <th scope="col" class="px-6 py-3">Assignee</th>
                <th scope="col" class="px-6 py-3">Due Date</th>
                <th scope="col" class="px-6 py-3">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($tasks as $task)
            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                    <a href="{{ route('tasks.show', $task) }}" class="hover:underline">
                        {{ $task->name }}
                    </a>
                </th>
                <td class="px-6 py-4">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</td>
                <td class="px-6 py-4">{{ ucfirst($task->priority) }}</td>
                <td class="px-6 py-4">{{ $task->assignee->name ?? 'Unassigned' }}</td>
                <td class="px-6 py-4">{{ $task->due_date ? $task->due_date->format('M d, Y') : 'N/A' }}</td>
                <td class="px-6 py-4 flex items-center space-x-4 justify-start">
                    @can('update', $task)
                        <a href="{{ route('tasks.edit', $task) }}" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Edit</a>
                    @endcan
                    @can('delete', $task)
                        <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="font-medium text-red-600 dark:text-red-500 hover:underline" onclick="return confirm('Are you sure you want to delete this task?')">
                                Delete
                            </button>
                        </form>
                    @endcan
                </td>
            </tr>
            @empty
            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                    No tasks found for this project.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
