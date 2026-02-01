// test message pop up when browser loads
console.log("System check: Leave requests scripts are loaded");

//date checker tool
function validateDates() {
    // find start and end dates from the form
    var start = document.getElementsByName("start_date")[0].value;
    var end = document.getElementsByName("end_date")[0].value;

    // convert to date objects to objects with " date value" property
    var startDate = new Date(start);
    var endDate = new Date(end);

    // is it the end date before the start?
    if (endDate.getTime() < startDate.getTime()) {
        // pop up warning
        alert("'End date'cannot be before 'Start date'");

        // prevent form submission
        return false;
    }
    // allow form submission, if everything is fine
    return true;
}
