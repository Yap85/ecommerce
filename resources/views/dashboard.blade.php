
<style>
    #newButton, #close
    {
        background-color: #5cb75c;
        color: white;
        padding: 5px 10px;
        font-size: 15px;
        font-weight: bold;
    }
</style>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div style="display:flex; width:100%; justify-content:space-between; padding: 10px;">
        <h2> Laravel </h2>
        <button id="newButton"class="align-right" onclick="location.href='/newProduct'">Create New Product</button>
    </div>


    <input type="text" id="search-input" placeholder="Search...">
    <button type="button" id="search-button" onclick="filterMainData()">Search</button>

    
    <div id="table"></div>

    <div id="modalDetails" class="modal fade" role="dialog">
        <div class="modal-dialog modal-primary modal-lg" role="document">
            <div class="modal-content" style="border: 0;">
                <div class="modal-body" style="padding: 0">
                    <div class="card" style="padding: 30px;">
                        <div>
                            <h4> Name: <p id="prd-name"></p></h4>
                            <h4> Price: <p id="prd-price"></p></h4>
                            <h4> Details: <p id="prd-detail"></p></h4>
                            <h4> Publish: <p id="prd-publish"></p></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script type="text/javascript">

    $(document).ready(function()
    {
        getMainData();
    });

    function getMainData(filter)
    {
        var containerId = "table";
        var data = utils.getDataTableDetails(containerId);
        var filter = $('#search-input').val();

        data["filter"] = filter;

        $.ajax({
            type: "GET",
            url: "/ajax/productList",
            data: data,
            success: function(data)
            {   
                loadMainData(containerId,data);
            }
        });
    }

    function loadMainData(containerId,data)
    {
        var mainData = data.results;

        var fields = [
                        ["prd_id","Product ID",true,false]
                        ,["prd_name","Name",false,false]
                        ,["price","Price (RM)",false,false]
                        ,["detail","Detail",false,false]
                        ,["publish","Publish",false,false]
                        ,["action","Action",false,false]
                    ]

        var table = utils.createDataTable(containerId,data,fields,sortMainData,pagingMainData);

        if(table != null && data.results.length > 0)
        {   
            var fieldPrdId = utils.getDataTableFieldIdx("prd_id",fields);
            var fieldName = utils.getDataTableFieldIdx("prd_name",fields);
            var fieldPrice = utils.getDataTableFieldIdx("price",fields);
            var fieldDetail = utils.getDataTableFieldIdx("detail",fields);
            var fieldPublish = utils.getDataTableFieldIdx("publish",fields);
            var fieldAction = utils.getDataTableFieldIdx("action",fields);

            for (var i = 1, row; row = table.rows[i]; i++)
            {
                var prdId = mainData[i - 1]["prd_id"];
                var publish = mainData[i - 1]["publish"];

                if(publish == 1)
                {
                    row.cells[fieldPublish].innerHTML = "Yes";
                }
                else
                {
                    row.cells[fieldPublish].innerHTML = "No";
                }

                row.cells[fieldAction].id = "action-btn";
                row.cells[fieldAction].innerHTML = "";

                var btnArea = document.createElement("div");
                btnArea.style.gap = "5px";
                btnArea.style.display = "flex";
                btnArea.style.justifyContent = "center";
                row.cells[fieldAction].appendChild(btnArea);

                var viewBtn = document.createElement("button");
                viewBtn.style.background = "#5cb75c";
                viewBtn.style.color = "white";
                viewBtn.style.padding = "5px";
                viewBtn.setAttribute('onclick','viewDetails('+prdId+')');
                viewBtn.innerHTML = "View";
                btnArea.appendChild(viewBtn);
                
                var editBtn = document.createElement("button");
                editBtn.style.background = "#0275d8";
                editBtn.style.color = "white";
                editBtn.style.padding = "5px";
                editBtn.onclick = function() {
                    window.location.href = "/updateProduct?id="+prdId
                };
                editBtn.innerHTML = "Edit";
                btnArea.appendChild(editBtn);
                
                var delBtn = document.createElement("button");
                delBtn.style.background = "#d9534f";
                delBtn.style.color = "white";
                delBtn.style.padding = "5px";
                delBtn.setAttribute('onclick','deleteProduct('+prdId+')');
                delBtn.innerHTML = "Delete";
                btnArea.appendChild(delBtn);
            }
        }
    }

    function viewDetails(prdId)
    {
        var data = {};

        prdId = prdId.toString();

        data['prd_id'] = prdId;

        $.ajax({
            type: "GET",
            url: "/ajax/viewDetail",
            data: data,
            success: function(data) 
            {
                if(data.length > 0)
                {
                    var prdName = data[0]['prd_name'];
                    var prdPrice = data[0]['price'];
                    var prdDetail = data[0]['detail'];
                    var prdPublish = data[0]['publish'];

                    document.getElementById('prd-name').innerHTML = prdName;
                    document.getElementById('prd-price').innerHTML = prdPrice;
                    document.getElementById('prd-detail').innerHTML = prdDetail;
                    document.getElementById('prd-publish').innerHTML = prdPublish;

                    $('#modalDetails').modal("show");
                }
                else
                {
                    alert(data.msg);
                }
            }
        });
    }

    function deleteProduct(prdId)
    {
        var data = {};

        prdId = prdId.toString();

        data['id'] = prdId;

        $.ajax({
            type: "POST",
            url: "/ajax/deleteProduct",
            data: data,
            success: function(data) 
            {
                if(data.status == 1)
                {
                    alert("Success!");
                }
                else
                {
                    alert(data.msg);
                }
            }
        });

        filterMainData();
    }

    function sortMainData()
    {
        utils.prepareDataTableSortData(this.containerId,this.orderBy);

        getMainData();
    }

    function pagingMainData()
    {
        utils.prepareDataTablePagingData(this.containerId,this.page);

        getMainData();
    }

    function filterMainData()
    {
        utils.resetDataTableDetails("table");

        getMainData();
    }
</script>