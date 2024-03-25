<script>
    // export let pageTitle;
    import { inertia, page } from "@inertiajs/svelte";
    import Icons from "../components/icons.svelte";
    import { router } from "../../util";

    let role = $page.props.role

    let role_menus = {
        admin : ['Home', 'Classes', 'Students', 'Teachers', 'Subjects', 'Assessment Types', 'Assessments', 'Terms', 'Academic Sessions', 'Users', 'Settings'],
        editor : ['Assessments'],
    }



    const displayMenu = (role, menu) => {
                
        let menus = role_menus[role];
        return menus?.some((display) => menu === display );
    }

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
    <aside class="flex fixed inset-0 flex-col w-60 min-h-screen px-5 py-8 overflow-y-auto bg-white border-r border-gray-100 rtl:border-r-0 rtl:border-l dark:bg-gray-900 dark:border-gray-700 z-50">
        <div>
            <img class="w-auto h-16 " src="/images/logo.png" height="300" width="300" alt="">
        </div>
    
        <div class="flex flex-col justify-between flex-1 mt-3">
            <nav class="flex-1 -mx-3 space-y-3 mt-12">


                { #if displayMenu(role, 'Home') }
                    <a class="flex items-center px-3 py-2 text-gray-800 transition-colors duration-300 transform rounded-lg dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-gray-200 hover:text-gray-700" href="/dashboard" use:inertia>
                        <Icons icon="dashboard" />
                        <span class="mx-2 text-sm text-gray-800">Home</span>
                    </a>
                { /if }
               

                { #if displayMenu(role, 'Classes') }

                    <div class="relative">
                        <div class="flex items-center justify-between  hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-gray-200 hover:text-gray-700 cursor-pointer  transition-colors duration-300 transform rounded-lg px-3 py-2">
                            <div class="flex items-center text-gray-800 dark:text-gray-300">   
                                <Icons icon="book" />
                                <span class="mx-2 text-sm text-gray-800">School Management</span>
                            </div>
                            <Icons icon="chevron_down" className="fill-gray-600"/>
                        </div>
                       
                        <div class="ml-5">
                            <a class="flex items-center px-3 py-2 text-gray-800 transition-colors duration-300 transform" href="/classes" use:inertia>   
                                <span class="mx-2 text-xs text-gray-500">Levels</span>
                            </a>
                            <a class="flex items-center px-3 py-2 text-gray-800 transition-colors duration-300 transform rounded-lg dark:text-gray-300" href="/subjects" use:inertia>
                                <span class="mx-2 text-xs text-gray-500">Courses</span>
                            </a>
                            <a class="flex items-center px-3 py-2 text-gray-800 transition-colors duration-300 transform rounded-lg dark:text-gray-300" href="/students" use:inertia>
                                <span class="mx-2 text-xs text-gray-500">Students</span>
                            </a>
                            <a class="flex items-center px-3 py-2 text-gray-800 transition-colors duration-300 transform rounded-lg dark:text-gray-300" href="/teachers" use:inertia>
                                <span class="mx-2 text-xs text-gray-500">Lecturers</span>
                            </a>
                            <a class="flex items-center px-3 py-2 text-gray-800 transition-colors duration-300 transform rounded-lg dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-gray-200 hover:text-gray-700" href="/terms" use:inertia>
                                <span class="mx-2 text-xs text-gray-500">Semesters</span>
                            </a>
                            <a class="flex items-center px-3 py-2 text-gray-800 transition-colors duration-300 transform rounded-lg dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-gray-200 hover:text-gray-700" href="/academic-sessions" use:inertia>
                                <span class="mx-2 text-xs text-gray-500">Sessions</span>
                            </a>
                        </div>


                    </div>

                { /if }
    
                { #if displayMenu(role, 'Assessment Types') }

                    <div class="relative">
                        <div class="flex items-center justify-between  hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-gray-200 hover:text-gray-700 cursor-pointer  transition-colors duration-300 transform rounded-lg px-3 py-2">
                            <div class="flex items-center text-gray-800 dark:text-gray-300">   
                                <Icons icon="badge" />
                                <span class="mx-2 text-sm text-gray-800">CBT Management</span>
                            </div>
                            <Icons icon="chevron_down" className="fill-gray-600"/>
                        </div>
                    
                        <div class="ml-5">
                            <a class="flex items-center px-3 py-2 text-gray-800 transition-colors duration-300 transform rounded-lg dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-gray-200 hover:text-gray-700" href="/assessment-types" use:inertia>
                                <span class="mx-2 text-xs text-gray-500">Assessment Types</span>
                            </a>
                            <a class="flex items-center px-3 py-2 text-gray-800 transition-colors duration-300 transform rounded-lg dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-gray-200 hover:text-gray-700" href="/assessments" use:inertia>
                                <span class="mx-2 text-xs text-gray-500">Assessments</span>
                            </a>
                        </div>
                    </div>
                   
                { /if }

    
                { #if displayMenu(role, 'Users') }
                    <a class="flex items-center px-3 py-2 text-gray-800 transition-colors duration-300 transform rounded-lg dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-gray-200 hover:text-gray-700" href="#">
                        <Icons icon="user" />
                        <span class="mx-2 text-sm">Users</span>
                    </a>
                { /if }
              

                { #if displayMenu(role, 'Settings') }
                    <a class="flex items-center px-3 py-2 text-gray-800 transition-colors duration-300 transform rounded-lg dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-gray-200 hover:text-gray-700" href="#">
                        <Icons icon="settings" />
                        <span class="mx-2 text-sm">Settings</span>
                    </a>
                { /if }
                               
            </nav>
        </div>
    </aside>

    <div class="ml-60 min-h-screen w-full flex flex-col bg-gray-50">
        <div class="fixed flex items-center w-screen h-20 border-b border-gray-100 pl-[18.7rem] pr-8 bg-white  inset-0 justify-between z-40">
            <div class="text-gray-800 font-semibold text-2xl"></div>
            
            <div class="flex items-center justify-between space-x-4">
                
                <a href="#" class="flex items-center gap-x-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200 capitalize">{ role }</span>
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



   