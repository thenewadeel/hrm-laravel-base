<div class="overflow-x-auto shadow-md rounded-xl">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Account Name</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Account Type</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Balance</th>
                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Edit</span></th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">{{-- Loop through accounts here --}}@forelse([] as $account)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $account->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $account->type }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$0.00</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"><a href="#"
                            class="text-indigo-600 hover:text-indigo-900">Edit</a></td>
                </tr>@empty<tr>
                        <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No accounts
                            found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
