function cpf7CopyValueAndFilter(idFrom, idTo, filterId, filterTo, filter2Id, filter2To)
{
    cpf7CopyValue(idFrom, idTo);
    cpf7FilterValues(idFrom, filterId, filter2Id);
    cpf7CopyValue(filterId, filterTo);
    cpf7CopyValue(filter2Id, filter2To);
}

function cpf7CopyValue(idFrom, idTo) {
       
        var selectedFrom = document.getElementById(idFrom.trim());
        var insertTo = document.getElementById(idTo.trim());
        if (selectedFrom!=null && insertTo!=null && typeof(insertTo) != "undefined" && typeof (selectedFrom) != "undefined") {

            var x = selectedFrom.selectedIndex;
            insertTo.value = selectedFrom[x].value;
        }
}


function cpf7FilterValues(idTable, idColumn, idColumn2) {

    var selectedTable = document.getElementById(idTable.trim());
    var insertTable = document.getElementById(idColumn.trim());
    var insertTable2 = document.getElementById(idColumn2.trim());
    

    if (selectedTable != null && insertTable != null && insertTable2 != null && typeof (insertTable) != "undefined" && typeof (insertTable2) != "undefined" && typeof (selectedTable) != "undefined") {
        var x = selectedTable.selectedIndex;
        var selectedTable = selectedTable[x].value;
        var minV = insertTable.length + 1;
        for (var i = 0; i < insertTable.length; i++)
        {
            var column = insertTable[i];
            
            if(column.value.indexOf(selectedTable)>-1)
            {
                column.style.display = "initial";
                if (i < minV)
                    minV = i;
            }
            else
            {
                column.style.display = "none";
            }
        }
        if (minV < insertTable.length + 1)
            insertTable.selectedIndex = minV;

        var minV = insertTable2.length + 1;
        for (var i = 0; i < insertTable2.length; i++) {
            var column = insertTable2[i];

            if (column.value.indexOf(selectedTable) > -1) {
                column.style.display = "initial";
                if (i < minV)
                    minV = i;
            }
            else {
                column.style.display = "none";
            }
        }
        if (minV < insertTable2.length + 1)
            insertTable2.selectedIndex = minV;
        
    }
}

