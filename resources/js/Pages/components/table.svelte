<script>
    export let headings = [];
</script>

<div class="flex flex-col border rounded w-full">
    <div class="-m-1.5">
      <div class="p-1.5 min-w-full inline-block align-middle">
        <div class="">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-100">
              <tr class="">
                { #each headings as heading }
                    <th scope="col" class="px-4 py-3 text-left text-sm font-bold text-gray-500 uppercase">{ heading }</th>
                {/each}
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <slot></slot>
            </tbody>
          </table>
        </div>
      </div>
    </div>
</div>