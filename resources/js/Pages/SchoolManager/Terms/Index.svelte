<script>
    import { onMount } from "svelte";
    import Button from "../../components/button.svelte";
    import DataTable from "../../components/data_table.svelte";
    import Icons from "../../components/icons.svelte";
    import Input from "../../components/input.svelte";
    import SlidePanel from "../../components/slide_panel.svelte";
    import Layout from "../../layouts/Layout.svelte";
    import { router } from "../../../util";


    let headings = ['SN', 'Semester', 'Actions']

    let terms = []

    let term = "";
    let termId;

    let disabled = false;

    onMount( () => {

        getTermList();

    })

    let showCreateSheet = false;

    const closeSheet = () => {
        showCreateSheet = false
        reset();
    }

    const showSheet = () => showCreateSheet = true

    const reset = () => {
        termId = undefined;
        term = "";

        disabled = false;
    }
    const getTermList = () => {

        router.get('/api/terms', {
            onSuccess : (response) => {
               terms = response.data
            }
        })
    }

    const createTerm = () => {

        disabled = true;

        router.post('/api/term/create', { term  }, {
            onSuccess: (response) => {
                
                getTermList();
                closeSheet();
                reset();

                disabled = false;

            },
            onError: (response) => {

                disabled = false;
                // console.log(response)
            }
        })
    }

    const updateTerm = () => {

        disabled = true;

        router.post('/api/term/update', { term, termId }, {
            onSuccess: (response) => {
                
                getTermList();
                closeSheet();
                reset();

                disabled = false;

            },
            onError: (response) => {

                disabled = false;
                // console.log(response)
            }
        })
    }

    const editTerm = (id, name) => {
        termId = id
        term = name
        showSheet();
    }

    const deleteClass = (id, name) => {
        termId = id
        term = name
    }

    $: disabled = ( term.length === 0 );

</script>
<Layout>
    <div class="my-28 container">

        <div class="flex items-center justify-between">
            <div class="flex space-x-3 items-center">
                <Icons icon="schedule" className="h-6 w-6" />
                <span class="mx-2 text-lg font-semibold text-gray-800">Semesters</span>
            </div>
            <div>
                <Button on:click={ showSheet } buttonText="New Semester" className="text-sm" />
            </div>
        </div>
    
        <DataTable { headings } >
            { #each terms as term,index(term.id) }
                <tr>
                    <td class="px-4 py-4 text-sm text-gray-800 dark:text-gray-300 whitespace-nowrap">{ index + 1  }</td>
                    <td class="px-4 py-4 text-sm text-gray-800 dark:text-gray-300 whitespace-nowrap">{ term.term }</td>
                    <td class="px-4 py-4 text-sm text-gray-800 dark:text-gray-300 whitespace-nowrap">
                        <div class="flex space-x-3">
                            <Button on:click={ () => editTerm(term.id, term.term) } buttonText="Edit" className="text-xs w-20" />
                            <Button buttonText="Delete" className="text-xs w-20 bg-red-500 hover:bg-red-400 focus:bg-red-500 focus:ring focus:ring-red-500 focus:ring-opacity-50" />
                        </div>
                    </td>
                </tr>
            { /each }
        </DataTable>
    </div>
</Layout>

<SlidePanel title="Create New Semester" showSheet={ showCreateSheet } on:close-button={ closeSheet } >
    <div class="flex flex-col space-y-6 p-3">
        <div>
            <Input value={ term } on:input={ (e) => term =  e.detail.input } label="Enter Semester Title" placeholder="First Semester" />
        </div>
        <div class="w-20">
            { #if termId }
                <Button { disabled } on:click={ updateTerm } buttonText="Update" className="text-sm"/>
            { :else }
                <Button { disabled } on:click={ createTerm } buttonText="Save" className="text-sm"/>
            {/if}
           
        </div>
    </div>
</SlidePanel>