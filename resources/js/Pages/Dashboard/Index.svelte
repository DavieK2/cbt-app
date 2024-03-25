<script>
    import { onMount } from "svelte";
    import Layout from "../layouts/Layout.svelte";

   
    const getDays = () => {

        let days = [];

        for (let index = 1; index <= 35; index++) {
           days.push(index);   
        }

        return days;
    }
</script>

<Layout>
    <div class="grid grid-cols-4 gap-4 mt-24 p-8">
        <div class="flex flex-col w-full justify-center h-40 border rounded-lg bg-white px-8 space-y-2 hover:scale-105 transition">
            <span class=" text-gray-600 text-sm">Total Students</span>
            <span class="text-3xl text-gray-800 font-semibold">1,000</span>
        </div>
        <div class="flex flex-col w-full justify-center h-40 border rounded-lg bg-white px-8 space-y-2 hover:scale-105 transition">
            <span class=" text-gray-600 text-sm">Total Classes</span>
            <span class="text-3xl text-gray-800 font-semibold">0</span>
        </div>
        <div class="flex flex-col w-full justify-center h-40 border rounded-lg bg-white px-8 space-y-2 hover:scale-105 transition">
            <span class=" text-gray-600 text-sm">Total Subjects</span>
            <span class="text-3xl text-gray-800 font-semibold">0</span>
        </div>
        <div class="flex flex-col w-full justify-center h-40 border rounded-lg bg-white px-8 space-y-2 hover:scale-105 transition">
            <span class=" text-gray-600 text-sm">Total Assessments</span>
            <span class="text-3xl  text-gray-800 font-semibold">0</span>
        </div>
    </div>

    <div class="flex flex-row mt-4 px-8 space-x-8 py-4">
        <div class="px-4 py-4 border rounded-lg bg-white">
            <div class="flex justify-between items-center h-10 pb-4">
                <span class="text-gray-800 text-base">August 2023</span>
                <div class="flex space-x-1">
                    <button class="flex items-center justify-center h-6 w-6 bg-gray-800 rounded">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M15.7071 19.2071C16.0976 18.8166 16.0976 18.1834 15.7071 17.7929L9.91421 12L15.7071 6.20711C16.0976 5.81658 16.0976 5.18342 15.7071 4.79289C15.3166 4.40237 14.6834 4.40237 14.2929 4.79289L8.5 10.5858C7.71895 11.3668 7.71895 12.6332 8.5 13.4142L14.2929 19.2071C14.6834 19.5976 15.3166 19.5976 15.7071 19.2071Z" fill="white"/>
                        </svg>
                    </button>
                    <button class="flex items-center justify-center h-6 w-6 bg-gray-800 rounded">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M8.04289 19.2071C7.65237 18.8166 7.65237 18.1834 8.04289 17.7929L13.8358 12L8.04289 6.20711C7.65237 5.81658 7.65237 5.18342 8.04289 4.79289C8.43342 4.40237 9.06658 4.40237 9.45711 4.79289L15.25 10.5858C16.031 11.3668 16.031 12.6332 15.25 13.4142L9.45711 19.2071C9.06658 19.5976 8.43342 19.5976 8.04289 19.2071Z" fill="white"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="h-80 w-96 border rounded overflow-hidden bg-white">
                <div class="grid grid-cols-7 bg-gray-50 text-gray-800">
                    <div class="flex items-center justify-center h-10 w-full border-r border-b text-xs font-semibold">Mon</div>
                    <div class="flex items-center justify-center h-10 w-full border-r border-b text-xs font-semibold">Tue</div>
                    <div class="flex items-center justify-center h-10 w-full border-r border-b text-xs font-semibold">Wed</div>
                    <div class="flex items-center justify-center h-10 w-full border-r border-b text-xs font-semibold">Thu</div>
                    <div class="flex items-center justify-center h-10 w-full border-r border-b text-xs font-semibold">Fri</div>
                    <div class="flex items-center justify-center h-10 w-full border-r border-b text-xs font-semibold">Sat</div>
                    <div class="flex items-center justify-center h-10 w-full border-b text-xs font-semibold">Sun</div>
                </div>

                <div class="grid grid-cols-7 grid-rows-5">
                    { #each getDays() as day } 
                        { #if day % 7 == 0 }
                            <div class="flex items-center justify-center h-14 w-full border-b text-xs  text-gray-700 ">{ day }</div>
                        { :else }
                            <div class="flex items-center justify-center h-14 w-full border-r border-b text-xs text-gray-700 ">{ day }</div>
                        {/if}
                    {/each}
                </div>
            </div>
        </div>
        <div class="flex flex-col border rounded-lg w-full h-96 bg-white">

        </div>
    </div>
</Layout>



   