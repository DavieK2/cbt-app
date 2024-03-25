<script>
    import { inertia, page } from "@inertiajs/svelte";
    import Icons from "../../../components/icons.svelte";
    import { router } from "../../../../util";

    let role = $page.props.role

    $: {

        if( ! localStorage.getItem('token') && role == 'admin' ){
            router.navigateTo(`/adminer/login`)
        } 

        if( ! localStorage.getItem('token') && role == 'teacher' ){
            router.navigateTo(`/teacher/login`)
        }
        
    }

    const logout = () => {

        router.post(`/user/logout`, {}, {
            onSuccess : (res) => {
                if( role == 'admin' ){
                    router.navigateTo(`/adminer/login`)
                } 
                if( role == 'teacher' ){
                    router.navigateTo(`/teacher/login`)
                }
            }
        })
    }

</script>

<div class="flex">
    <aside class="flex fixed inset-0 flex-col w-56 min-h-screen px-5 py-8 overflow-y-auto bg-white border-r rtl:border-r-0 rtl:border-l dark:bg-gray-900 dark:border-gray-700 z-50">
        <div>
            <img class="w-auto h-16 " src="/images/logo.png" height="300" width="300" alt="">
        </div>
    
        <div class="flex flex-col justify-between flex-1 mt-6">
            <nav class="flex-1 -mx-3 space-y-3 mt-12">
                <a class="flex items-center px-3 py-2 text-gray-800 transition-colors duration-300 transform rounded-lg dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-gray-200 hover:text-gray-700" href="/teacher/dashboard" use:inertia>
                    <Icons icon="dashboard" />
                    <span class="mx-2 text-sm">Home</span>
                </a>

                <a class="flex items-center px-3 py-2 text-gray-800 transition-colors duration-300 transform rounded-lg dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-gray-200 hover:text-gray-700" href="/teacher/subject-topics" use:inertia>
                    <Icons icon="book" />
                    <span class="mx-2 text-sm">Subject Topics</span>
                </a>
    
                <a class="flex items-center px-3 py-2 text-gray-800 transition-colors duration-300 transform rounded-lg dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-gray-200 hover:text-gray-700" href="#">
                    <Icons icon="settings" />
                    <span class="mx-2 text-sm">Setting</span>
                </a>
            </nav>
    
            <!-- <div class="mt-6">
                <div class="p-3 bg-gray-100 rounded-lg dark:bg-gray-800">
                    <h2 class="text-sm text-gray-800 dark:text-white">New feature availabel!</h2>
    
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Lorem ipsum dolor sit amet consectetur adipisicing elit. Natus harum officia eligendi velit.</p>
    
                    <img class="object-cover w-full h-32 mt-2 rounded-lg" src="https://images.unsplash.com/photo-1658953229664-e8d5ebd039ba?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1374&h=1374&q=80" alt="">
                </div>
            </div> -->
        </div>
    </aside>

    <div class="ml-56 min-h-screen w-full flex flex-col bg-gray-50">
        <div class="fixed flex items-center w-screen h-20 border-b pl-[18.7rem] pr-8 bg-white  inset-0 justify-between z-40">
            <div class="text-gray-800 font-semibold text-2xl"></div>
            <div class="flex items-center justify-between space-x-4">
                
                <a href="#" class="flex items-center gap-x-2">
                    <img class="object-cover rounded-full h-7 w-7" src="https://images.unsplash.com/photo-1531427186611-ecfd6d936c79?ixid=MXwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHw%3D&ixlib=rb-1.2.1&auto=format&fit=crop&w=634&h=634&q=80" alt="avatar" />
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">John Doe</span>
                </a>
                
                <button on:click={ logout } class="text-gray-500 transition-colors duration-200 rotate-180 dark:text-gray-400 rtl:rotate-0 hover:text-blue-500 dark:hover:text-blue-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                    </svg>
                </button>
            </div>
        </div>

        <slot></slot>
    </div>
</div>



   