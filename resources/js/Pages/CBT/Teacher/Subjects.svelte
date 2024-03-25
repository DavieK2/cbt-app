<script>
    import { onMount } from "svelte";
    import DataTable from "../../components/data_table.svelte";
    import Layout from "./components/layout.svelte";
    import { router } from "../../../util";
    import { page } from "@inertiajs/svelte";
    import Button from "../../components/button.svelte";


    let headings = ['S/N', 'Subject', 'Actions'];

    let subjects = [];

    let classCode = $page.props.classCode;

    onMount(() => {
        router.getWithToken('/api/get-subjects/', {
            onSuccess : (res) => {
                subjects = res.data
            }
        })
    })

    const goToAssessmentQuestion = (subjectId) => {
        router.navigateTo(`/teacher/create-questions/${classCode}/${subjectId}`);
    }

</script>

<Layout>
    <div class="px-6 my-28">
        <div class="container px-4">
            <h3 class="text-lg font-semibold">Classes</h3>
        </div>
        <DataTable { headings } >
            { #each subjects as subject,index(subject.subjectId) }
                <tr>
                    <td class="px-4 py-4 text-sm text-gray-800 dark:text-gray-300 whitespace-nowrap">{ index + 1  }</td>
                    <td class="px-4 py-4 text-sm text-gray-800 dark:text-gray-300 whitespace-nowrap">{ subject.subjectName }</td>
                    <td class="px-4 py-4 text-sm text-gray-800 dark:text-gray-300 whitespace-nowrap">
                        <div class="w-40">
                            <Button on:click={ () => goToAssessmentQuestion(subject.subjectId) } buttonText="Set Questions" />
                        </div>
                    </td>
                </tr>
            { /each }
        </DataTable>
    </div>
</Layout>