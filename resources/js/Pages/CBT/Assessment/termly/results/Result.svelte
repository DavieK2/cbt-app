<script>
    import { onMount } from "svelte";
    import Layout from "../../../../layouts/Layout.svelte";
    import { router } from "../../../../../util";
    import { page } from "@inertiajs/svelte";
    import { jsPDF } from "jspdf"
    import Button from "../../../../components/button.svelte";

    let studentData;

    let isLoading = true;
    let assessmentId = $page.props.assessmentId;
    let assessmentTitle = $page.props.assessmentTitle;
    let studentId = $page.props.studentId;

    onMount(() => {

        isLoading = true;

        router.post('/api/assessment/student/results', { assessmentId, studentId }, {
            onSuccess : (res) => {
                studentData = res.data
                isLoading = false;
            }
        });
    });

    const downloadPDF = () => {
        let result = document.getElementById('result');

        let pdf = new jsPDF('p', 'px', [1040, 1400]);

        pdf.html(result, {
            callback: function (doc) {
                doc.save(`${studentData?.studentName}.pdf`);
            },
            autoPaging: false,
            x: 10,
            y: 10
        });
    }
</script>

<Layout>
    <div class="container">
        <div class="my-28">
            <div class="flex items-center justify-between w-full bg-white rounded-lg border border-gray-200 h-20 px-6 text-gray-800">
                <div class="text-xl font-bold">Student Result</div>
                <Button on:click={ downloadPDF } buttonText="Download PDF" className="max-w-fit min-w-max" />
            </div>
                <div class="flex w-full justify-center">
                    { #if isLoading === false }
                        <div id="result" class="mt-12 w-[65rem] h-full p-8 rounded-lg bg-white border border-gray-200 text-gray-800 relative">
                           <!-- <img src="/images/soncal_logo.jpeg" class="absolute inset-0 mx-auto my-auto opacity-5 object-cover" alt=""/> -->
                           <div class="flex flex-col w-full space-y-4 items-center pt-4">
                                <img src="/images/soncal_logo.jpeg" alt="" class="rounded-lg" height="160" width="160" />
                                <div class="pb-12 text-3xl font-extrabold uppercase text-center">{ assessmentTitle } Report</div>
                           </div>
                            <div class="p-4">
                                <div class="flex justify-between items-center">
                                    <img src={ studentData?.studentPhoto } alt="" class="rounded-lg" height="200" width="200" />
                                </div>
                                <div class="mt-8 space-y-3 uppercase">
                                    <p class="font-bold text-gray-800">Student Name : <span class="font-normal text-gray-700"> { studentData?.studentName }</span></p>
                                    <p class="font-bold text-gray-800">Reg No : <span class="font-normal text-gray-700">{ studentData?.studentId }</span></p>
                                    <p class="font-bold text-gray-800">Level : <span class="font-normal text-gray-700">{ studentData?.studentClass }</span></p>
                                </div>
                            </div>

                            <table class="w-full mt-12 z-50">
                                <thead class="uppercase bg-gray-100">
                                    <th class="p-4 text-left">S/N</th>
                                    <th class="p-4 text-left">Course Name</th>
                                    <th class="p-4 text-left">Course Code</th>
                                    <th class="p-4 text-left">Score</th>
                                    <th class="p-4 text-left">Grade</th>
                                    <th class="p-4 text-left">Remarks</th>
                                </thead>
                                <tbody>
                                    { #each studentData?.studentResults ?? [] as result, index }
                                        <tr>
                                            <td class="p-4"> { index + 1 }</td>
                                            <td class="p-4"> { result.subjectName }</td>
                                            <td class="p-4"> { result.subjectCode }</td>
                                            <td class="p-4"> { result.score }</td>
                                            <td class="p-4"> { result.grade }</td>
                                            <td class="p-4"> { result.remarks }</td>
                                        </tr>
                                    {/each}
                                </tbody>
                            </table>
                        </div>
                    { /if }
                </div>
            </div>
        </div>
</Layout>