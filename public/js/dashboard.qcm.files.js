$("#uploadField").change(function () {


    //$('#filesTable>tbody>tr').remove();
    $("#filesTable>tbody").find("tr:gt(0)").remove();

    let names = [];
    for (var i = 0; i < $(this).get(0).files.length; ++i) {
        let file = $(this).get(0).files[i];
        let flag = "non ";
        if(file.type == 'text/html')
            flag = "oui";

        $('#filesTable>tbody>tr:last').after('<tr class="'+flag+'"><td>'+i+'</td><td>'+file.name+'</td><td>'+file.type+'</td><td>'+(file.size/1000).toFixed(1)+' Ko</td><td>'+flag+'</td></tr>');
    }


});