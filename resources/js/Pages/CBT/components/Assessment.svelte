<script>

    import { inertia } from "@inertiajs/svelte"
    export let step;

</script>

<div class="container mx-auto pt-4 px-4 sm:px-6 lg:px-8 my-8">
    <div class="w-full bg-white dark:bg-gray-800 py-5 flex flex-col xl:flex-row items-start xl:items-center justify-between px-5 xl:px-10 shadow rounded-lg">
        <div class="mb-4 sm:mb-0 md:mb-0 lg:mb-0 xl:mb-0 lg:w-1/2">
            <h2 class="text-gray-800 dark:text-gray-100 text-lg font-bold">
               { step.placeholder }
            </h2>
            <p class="font-normal text-sm text-gray-600 dark:text-gray-100 mt-1">
                New Assessment
            </p>
        </div>
        <div class="w-full">
            <div class="xl:w-full xl:mx-0 h-12 hidden sm:block bg-white">
                <ul class="flex justify-end gap-8">
                    <a use:inertia href={ step.stepOneUrl } class={`group hover:border-gray-800 hover:shadow ease-linear transition-all duration-150 text-sm flex flex-col justify-between border pt-3 rounded-t font-normal cursor-pointer ${ step.step == 1 ? 'border-gray-800' : ' border-gray-400'}`}>
                        <div class="flex items-center px-8 mb-2 space-x-2">
                            <div class={`w-6 h-6 group-hover:bg-gray-800 rounded-full transition flex items-center justify-center ${ step.step == 1 ? 'bg-gray-800' : 'bg-gray-300'}`}>
                                <p class="focus:outline-none text-xs text-white">1</p>
                            </div>
                            <span class="ltr:ml-2 rtl:mr-2 group-hover:text-primary text-gray-600">Details</span>
                        </div>
                        <div class={`w-full h-1 group-hover:bg-gray-800 transition rounded-t-md ${ step.step == 1 ? 'bg-gray-800' : 'bg-gray-300'}`}/>
                    </a>
                    <a use:inertia href={ step.stepTwoUrl } class={`group hover:border-gray-800 hover:shadow ease-linear transition-all duration-150 text-sm flex flex-col justify-between border pt-3 rounded-t font-normal cursor-pointer border-gray-400 ${ step.step == 2 ? 'border-gray-800' : 'border-gray-300'}`}>
                        <div class="flex items-center px-8 mb-2 space-x-2">
                            <div class={`w-6 h-6 group-hover:bg-gray-800 rounded-full flex items-center transition justify-center ${ step.step == 2 ? 'bg-gray-800' : 'bg-gray-300'}`}>
                                <p class="focus:outline-none text-xs text-white">2</p>
                            </div>
                            <span class="ltr:ml-2 rtl:mr-2 group-hover:text-primary text-gray-600">Levels</span>
                        </div>
                        <div class={`w-full h-1 group-hover:bg-gray-800 transition rounded-t-md ${ step.step == 2 ? 'bg-gray-800' : 'bg-gray-300'}`}/>
                    </a>
                    <a use:inertia href={ step.stepThreeUrl } class={`group hover:border-gray-800 hover:shadow ease-linear transition-all duration-150 text-sm flex flex-col justify-between border pt-3 rounded-t font-normal cursor-pointer ${ step.step == 3 ? 'border-gray-800' : ' border-gray-400'}`}>
                        <div class="flex items-center px-8 mb-2 space-x-2">
                            <div class={`w-6 h-6 group-hover:bg-gray-800 transition rounded-full flex items-center justify-center ${ step.step == 3 ? 'bg-gray-800' : 'bg-gray-300'}`}>
                                <p class="focus:outline-none text-xs text-white">
                                   3
                                </p>
                            </div>
                            <span class="ltr:ml-2 rtl:mr-2 group-hover:text-primary text-gray-600">Schedules</span>
                        </div>
                        <div class={`w-full h-1 group-hover:bg-gray-800 transition rounded-t-md ${ step.step == 3 ? 'bg-gray-800' : 'bg-gray-300'}`}/>
                    </a>
                </ul>
            </div>
        </div>
    </div>
</div>

<slot></slot>
