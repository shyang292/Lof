
require(['jquery'], function ($) {
    $(document).ready(function($) {
        // var mywindow = window.open('', 'PRINT');
        // mywindow.document.write("<html><body>");
        // mywindow.document.write(document.getElementById('quoteTable').innerHTML);
        // mywindow.document.write('</body></html>');
        // mywindow.document.close(); // necessary for IE >= 10
        // mywindow.focus(); // necessary for IE >= 10*/
        // mywindow.print();
        // mywindow.close();
        // return true;

        function printQuote() {
            var w = window.open();
            var html = $("#quoteTable").html();
            $(w.document.body).html(html);
        }

    });
});