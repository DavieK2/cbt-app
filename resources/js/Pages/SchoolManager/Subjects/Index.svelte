<script>
    import { onMount } from "svelte";
    import Button from "../../components/button.svelte";
    import DataTable from "../../components/data_table.svelte";
    import Icons from "../../components/icons.svelte";
    import Input from "../../components/input.svelte";
    import SlidePanel from "../../components/slide_panel.svelte";
    import Layout from "../../layouts/Layout.svelte";
    import { router } from "../../../util";


    let headings = ['SN', 'Course Name', 'Course Code', 'Actions'];

    let subjects = []

    let subjectName = "";
    let subjectCode = "";
    let subjectId;

    let disabled = false;

    onMount( () => {

        getSubjectList();

    })

    let showCreateSubjectSheet = false;

    const closeSheet = () => {
        showCreateSubjectSheet = false
        reset();
    }

    const showSheet = () => showCreateSubjectSheet = true

    const reset = () => {
        subjectId = undefined;
        subjectName = "";
        subjectCode = "";
    }

    const getSubjectList = () => {

        router.get('/api/subjects', {
            onSuccess : (response) => {
               subjects = response.data
            }
        })
    }

    const createSubject = () => {

        disabled = true;

        router.post('/api/subject/create', { subjectName, subjectCode }, {
            onSuccess: (response) => {
                
                getSubjectList();
                reset();
                closeSheet();

                disabled = false

            },
            onError: (response) => {

                disabled = false
                // console.log(response)
            }
        })
    }

    const updateSubject = () => {

        disabled = true;

        router.post('/api/subject/update', { subjectName, subjectId, subjectCode }, {
            onSuccess: (response) => {

                getSubjectList();
                reset();
                closeSheet();

                disabled = false;

            },
            onError: (response) => {

                disabled = false
                // console.log(response)
            }
        })
    }

    const editSubject = (id, name, code) => {
        subjectId = id
        subjectName = name
        subjectCode = code
        showSheet();
    }

    const deleteSubject = (id, name) => {
        subjectId = id
        subjectName = name
    }

    $: disabled = ( subjectName.length === 0 );

</script>

<Layout>
    <div class="container my-28">
        <div class="flex items-center justify-between">
            <div class="flex space-x-3 items-center">
                <Icons icon="book" className="h-6 w-6" />
                <span class="mx-2 text-lg font-medium text-gray-800">Courses</span>
            </div>
            <div>
                <Button on:click={ showSheet } buttonText="New Course" className="text-sm" />
            </div>
        </div>
      
        <DataTable { headings } >
            { #each subjects as subject,index(subject.subjectId) }
                <tr>
                    <td class="px-4 py-4 text-sm text-gray-800 dark:text-gray-300 whitespace-nowrap">{ index + 1  }</td>
                    <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-300 whitespace-nowrap">{ subject.subjectName }</td>
                    <td class="px-4 py-4 text-sm text-gray-500 dark:text-gray-300 whitespace-nowrap">{ subject.subjectCode }</td>
                    <td class="px-4 py-4 text-sm text-gray-800 dark:text-gray-300 whitespace-nowrap">
                        <div class="flex space-x-3">
                            <Button on:click={ () => editSubject(subject.subjectId, subject.subjectName, subject.subjectCode) } buttonText="Edit" className="text-xs w-20" />
                            <Button buttonText="Delete" className="text-xs w-20 bg-red-500 hover:bg-red-400 focus:bg-red-500 focus:ring focus:ring-red-500 focus:ring-opacity-50" />
                        </div>
                    </td>
                </tr>
            {/each}
        </DataTable>
    </div>
</Layout>

<SlidePanel title="Create New Course" showSheet={ showCreateSubjectSheet } on:close-button={ closeSheet }>
    <div class="flex flex-col space-y-6 p-3">
        <div>
            <Input value={ subjectName } on:input={ (e) => subjectName = e.detail.input }  label="Enter Course Name" />
        </div>
        <div>
            <Input value={ subjectCode } on:input={ (e) => subjectCode = e.detail.input }  label="Enter Course Code" />
        </div>
        <div class="w-20">
            { #if subjectId }
                <Button { disabled } on:click={ updateSubject } buttonText="Update" className="text-sm"/>
            { :else }
                <Button { disabled } on:click={ createSubject } buttonText="Save" className="text-sm"/>
            { /if }
        </div>
    </div>
</SlidePanel>