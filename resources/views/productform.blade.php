<style>
    .back-button 
    {
        background-color: #025aa5;
        color: white;
        padding: 8px 20px;
        text-decoration: none;
        border-radius: 6px;
        display: inline-block;
    }
</style>
<x-app-layout>
    <form method="POST" id="newProductForm" style="padding:20px;">
        <div style="display:flex; width:100%; justify-content:space-between; padding: 10px;">
            <h2>Add New Product</h2>
            <a href="javascript:history.back()" class="back-button">Back</a>
        </div>
        <h4>Name:</h4>
        <div class="row" style="">
            <div style="">
                <input id="new_name" type="text" name="name" class="new-name" placeholder="Name">
            </div>
        </div>

        <br>

        <h4>Price (RM):</h4>
        <div class="row" style="">
            <div style="">
                <input id="new_price" type="number" name="price" class="new-price" placeholder="99.90">
            </div>
        </div>  

        <br>

        <h4>Detail:</h4>
        <div class="row" style="">
            <div style="">
                <textarea id="new_detail" name="detail" class="new-detail" rows="4" cols="50"></textarea>
            </div>
        </div>  

        <br>

        <h4>Publish:</h4>
        <div class="row" style="">
            <div>
                <input type="radio" id="yes" name="publish" value="1">
                <label for="yes">Yes</label>
            </div>
            <div>
                <input type="radio" id="no" name="publish" value="2">
                <label for="no">No</label>
            </div>
        </div>  

        <div class="row" style="justify-content: center;">
            <div style="text-align: center; background-color: #0275d8; color:white; width: 100px; padding:7px;">
                <button type="submit" class="edit-submit">
                    Submit
                </button>
            </div>
        </div>
    </form>
</x-app-layout>

<script type="text/javascript">

    $('#newProductForm').submit(function(e)
    {
        e.preventDefault();
        submitnewProductForm();
    });

    function submitnewProductForm()
    {
        var data = {};

        data['name'] = $('#new_name').val();
        data['price'] = $('#new_price').val();
        data['detail'] = $('#new_detail').val();
        data['publish'] = document.querySelector('input[name="publish"]:checked').value;

        $.ajax({
            type: 'POST',
            url: '/ajax/submit-form',
            data: data,
            success: function(data)
            {
                if(data.status == 1)
                {
                    alert("Success!");

                    window.location = "/dashboard";
                }
                else
                {
                    alert(data.error);
                }
            }
        });
    }

</script>