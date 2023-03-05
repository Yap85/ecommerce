(function(root) 
{
	//FUNCTION DECLARATION
	var utils = {
		//data table
		'createDataTable' : createDataTable,
		'createDataTablePaging' : createDataTablePaging,
		'prepareDataTableSortData' : prepareDataTableSortData,
		'prepareDataTablePagingData' : prepareDataTablePagingData,
		'getDataTableFieldIdx' : getDataTableFieldIdx,
		'getDataTableDetails' : getDataTableDetails,
		'resetDataTableDetails' : resetDataTableDetails,

		'addClass' : addClass,
		'removeClass' : removeClass,

		//submit button
		'startLoadingBtn' : startLoadingBtn,
		'stopLoadingBtn' : stopLoadingBtn,

		'showModal' : showModal,
		'createSpinner' : createSpinner, //spinner indicator
		'formatMoney' : formatMoney,
		'formatCurrencyInput' : formatCurrencyInput,
		'getParameterByName' : getParameterByName,

		//get date in db format
		'getTodayDB' : getTodayDB,
		'getDay' : getDay,
		'getMonth' : getMonth,

		//get date in yy-mm-dd
		'formattedDate' : formattedDate,
		'datepickerStart' : datepickerStart,
		'datepickerEnd' : datepickerEnd,

		'getCurrentDateTime' : getCurrentDateTime,
		'padLeft' : padLeft,

		//Logging
		'generateLogData' : generateLogData,
	};
	
	root.utils = utils;

	function createDataTable(containerId,dataSet,fields,callbackSort,callbackPaging,filterTabDiv, hidTotalRecords=0)
	{
		// fields structure
		// 0 - field name
		// 1 - field title
		// 2 - allow order
		// 3 - align right

		//container
	    var div = document.getElementById(containerId);
	    div.innerHTML = "";

	    if(filterTabDiv != null)
    	{
    		div.appendChild(filterTabDiv);
    	}

    	//table container
    	var divTableContainer = document.createElement("div");
    	divTableContainer.className = "table-responsive";
    	div.appendChild(divTableContainer); 

	    //table
	    var table = document.createElement("table");
		table.className = "table table-bordered table-sm";
		table.style.marginBottom = '0';	
		table.style.padding = '0';	
		divTableContainer.appendChild(table); 

		//table header
		var tHead = table.createTHead();
		var row = tHead.insertRow(0); 

		for (i = 0; i < fields.length; i++)
	    {
	    	var fieldName = fields[i][0];
	    	var fieldTitle = fields[i][1];
	    	var allowOrder = fields[i][2];

	    	var th = document.createElement('th');
			th.innerHTML = fieldTitle;

			if(allowOrder)
			{
				th.containerId = containerId;
				th.orderBy = fieldName;

				utils.addClass(th,'sorting');

				th.onclick = callbackSort;

				if(orderBy == fieldName)
				{
					if(orderType == "desc")
						utils.addClass(th,'sorting-desc');
					else
						utils.addClass(th,'sorting-asc');
				}
			}

			th.style.textAlign = 'center';
		    th.style.verticalAlign = "middle";
		    th.style.border = "none";
		    th.style.background = "#efefef";
		    th.style.padding = "5px";

			row.appendChild(th);
	    } 

	    //table data
	    var data = dataSet.results;

	    if(data != undefined && data.length > 0)
	    {
		    //order by data
		    var orderBy = div.orderBy;
	    	var orderType = div.orderType;

	    	//paging data
		    var page = div.page;
		    var pageSize = dataSet.page_size;
		    var dataSize = dataSet.count;


		    if(page == undefined)
		    {
		    	page = 1;
		    	div.pagination = page;
		    }

		    var ttlPage = Math.ceil(dataSize / pageSize);

		    if(ttlPage < 1)
		    	ttlPage = 1;

		    var aryPage = [];

		    for(var i = -2 ; i < 10 ; i++)
		    {
		    	var validPage = false;

		  		var tmpPage = page + i;

		    	if(tmpPage >= 1 && tmpPage <= ttlPage)
		    	{
		    		validPage = true;
		    	}

		    	if(validPage)
		    		aryPage.push(tmpPage);
		    }

		    //table data
			var tBody = table.createTBody();
			
		    for (i = 0; i < data.length; i++)
		    {
		        row = tBody.insertRow(i);
		        
		        for (j = 0; j < fields.length; j++)
		        {
		        	var alignRight = fields[j][3];

		            var cell = row.insertCell(j);

		            if(alignRight)
		            	cell.style.textAlign = "right";
		            else
		            	cell.style.textAlign = "center";

		            cell.style.verticalAlign = "middle";
		            cell.style.padding = "5px";

		            cell.innerHTML = data[i][fields[j][0]];                          
		        }                   
		    }

		    if(dataSize > 5)
		    {
		    	var navBottom = document.createElement("nav");
			    navBottom.style.padding = '20px';
			    navBottom.style.display = 'flex';
			    navBottom.style.justifyContent  = 'center';
				div.appendChild(navBottom);
				div.style.backgroundColor = "#fff";

				if(aryPage.length > 5)
				{
					var pageSize = 0;
					var chunk=[];
					var chunkSize = 5;
					for (let i = 0; i < aryPage.length; i += chunkSize) 
					{
						chunk[pageSize] = aryPage.slice(i, i + chunkSize);
						pageSize++;
					}
					aryPage = chunk[0];
				}

				createDataTablePaging(containerId,navBottom,page,aryPage,callbackPaging,ttlPage);
		    }

			return table;
		}
		else
		{
			var tBody = table.createTBody();
	        row = tBody.insertRow(0);
	        row.style = "text-align:center";
	        var cell = row.insertCell(0);

	        cell.colSpan = fields.length;
	        cell.style.padding = "20px";

			return table;
		}
	}
	
	function createDataTablePaging(containerId,nav,page,aryPage,callbackPaging,ttlPage)
	{
		var ul = document.createElement("ul");
		ul.className = "pagination";

		var imgTop = "|<";
		var imgBack = "<";
		var imgNext = ">";
		var imgLast = ">|";

		if(aryPage.length > 1)
		{
			nav.appendChild(ul);
		}
		
		var li = document.createElement("li");
		li.className = "page-item";
		if(page > 1)
		{
			li.containerId = containerId;
			li.page = 1;
			li.onclick = callbackPaging;
		}
		li.innerHTML = '<span class="page-link">'+imgTop+'</span>';
		ul.appendChild(li); 

		var li = document.createElement("li");
		li.className = "page-item";
		if(page > 1)
		{
			li.containerId = containerId;
			li.page = page - 1;
			li.onclick = callbackPaging;
		}
		li.innerHTML = '<span class="page-link">'+imgBack+'</span>';
		ul.appendChild(li); 
		

		for(var i = 0 ; i < aryPage.length ; i++)
	    {
	    	var li = document.createElement("li");
			li.className = "page-item";

			li.containerId = containerId;
			li.page = aryPage[i];
			li.onclick = callbackPaging;

			li.innerHTML = '<span class="page-link">' + aryPage[i] + '</span>';

			if(aryPage[i] == page)
				utils.addClass(li,"active");

			ul.appendChild(li); 
	    }

	    
		var li = document.createElement("li");
		li.className = "page-item";
		if(page != ttlPage)
		{
			li.containerId = containerId;
			li.page = page + 1;
			li.onclick = callbackPaging;
		}
		li.innerHTML = '<span class="page-link">'+imgNext+'</span>';
		ul.appendChild(li); 

		var li = document.createElement("li");
		li.className = "page-item";

		if(page != ttlPage)
		{
			li.containerId = containerId;
			li.page = ttlPage;
			li.onclick = callbackPaging;
		}
		li.innerHTML = '<span class="page-link">'+imgLast+'</span>';
		ul.appendChild(li); 
		
	}

	function prepareDataTableSortData(containerId,orderBy)
	{
		var div = document.getElementById(containerId);

    	var prevOrderBy = div.orderBy;
    	var prevOrderType = div.orderType;

		if(orderBy == prevOrderBy)
	    {
	        if(prevOrderType == "desc")
	        {
	            div.orderType = "asc";
	        }
	        else
	        {
	            div.orderType = "desc";
	        }
	    }
	    else
	    {
	        div.orderType = "desc";
	    }

	    div.orderBy = orderBy; 
	}

	function prepareDataTablePagingData(containerId,pageNo)
	{
		var div = document.getElementById(containerId);

    	div.page = pageNo;
	}

	function getDataTableFieldIdx(name,fields)
	{
	    for (i = 0; i < fields.length; i++)
	    {
	        if(name == fields[i][0])
	            return i;
	    }

	    return 0;
	}

	function getDataTableDetails(containerId)
	{
		var div = document.getElementById(containerId);

	    var data = {
                page : div.page
                ,order_by : div.orderBy
                ,order_type : div.orderType
                };

        return data;
	}

	function resetDataTableDetails(containerId)
	{
		var div = document.getElementById(containerId);

		div.page = null;
    	div.orderBy = null;
    	div.order_type = null;
	}

	function addClass(element,name) 
	{
	    var arr;
	    arr = element.className.split(" ");
	    if(arr.indexOf(name) == -1) 
	    {
	        element.className += " " + name;
	    }
	}

	function removeClass(element,name) 
	{
	    var arr;
	    arr = element.className.split(" ");

	    var idx = arr.indexOf(name);

	    if(idx >= 0) 
	    {
	        arr.splice(idx,1);
	    }

	    element.className = arr.join(" ");
	}

	function startLoadingBtn(element,overlayContainer) 
	{
		var btn = document.getElementById(element);

	    var ladda = Ladda.create(btn);
		ladda.start();

		//create overlay
		if(overlayContainer)
		{
			var div = document.createElement('div');
			div.id = overlayContainer + "_overlay";
	    	div.style.backgroundColor = "black";
	    	div.style.width = "100%";
	    	div.style.height = "100%";
	    	div.style.top = "0";
	    	div.style.left = "0";
	    	div.style.opacity = "0.2";
	    	div.style.position = "absolute";
	    
	    	document.getElementById(overlayContainer).appendChild(div);
		}
	}

	function stopLoadingBtn(element,overlayContainer) 
	{
		var btn = document.getElementById(element);

	    var ladda = Ladda.create(btn);
		ladda.stop();

		//remove overlay
		if(overlayContainer)
		{
			var overlay = document.getElementById(overlayContainer + "_overlay");
			overlay.parentNode.removeChild(overlay);
		}
	}

	function showModal(contentTitle,contentBody,type,callbackClose)
	{
	    var modal = document.createElement("div");
	    modal.className = "modal fade";
	    modal.setAttribute("role", "dialog");     

	    var dialog = document.createElement("div");

	    if(type == 1)
		{
			dialog.className = "modal-dialog modal-success";
		}
		else 
		{
			dialog.className = "modal-dialog modal-danger";
		}
	    
	    dialog.setAttribute("role", "document");   
	    modal.appendChild(dialog);              

	    var content = document.createElement("div");
	    content.className = "modal-content";
	    dialog.appendChild(content);   

	    var header = document.createElement("div");
	    header.className = "modal-header";
	    content.appendChild(header);   

	    var title = document.createElement("h4");
	    title.className = "modal-title";
	    title.innerHTML = contentTitle;
	    header.appendChild(title);

	    var btnX = document.createElement("button");
	    btnX.className = "close";
	    btnX.setAttribute("data-dismiss", "modal");
	    btnX.innerHTML = "×";
	    header.appendChild(btnX);

	    var body = document.createElement("div");
	    body.className = "modal-body";

	    if(Array.isArray(contentBody)) //is array
	    {
	    	var ul = document.createElement("ul");

	    	for(var i = 0 ; i < contentBody.length ; i++)
		    {
		    	var li = document.createElement("li");
		    	li.innerHTML = contentBody[i];
		    	ul.appendChild(li);
		    }

		    body.appendChild(ul);
	    }
	    else
	    {
	    	body.innerHTML = contentBody;
	    }

	    content.appendChild(body); 

	    var footer = document.createElement("div");
	    footer.className = "modal-footer";
	    content.appendChild(footer); 

	    var btnClose = document.createElement("button");
	    btnClose.className = "btn btn-secondary";
	    btnClose.setAttribute("data-dismiss", "modal");
	    btnClose.innerHTML = locale['utils.modal.close'];
	    footer.appendChild(btnClose);

	    $(modal).modal('show');

	    if(callbackClose)
	    {
	    	$(modal).on('hidden.bs.modal', function () {
			    callbackClose();
			});
	    }
	    
	    // speed up focus on close btn
	    setTimeout(function (){
	        $(btnClose).focus();
	    }, 150);

	    //fail safe to focus
	    $(modal).on('shown.bs.modal', function() {
			$(btnClose).focus();
		});
	}

	function createSpinner(element) 
	{
		var spinner = document.getElementById(element);

		var div = document.createElement('div');
		div.className = "sk-wave";
		spinner.appendChild(div);

		var rect;

		rect = document.createElement('div');
		rect.className = "sk-rect sk-rect1";
		div.appendChild(rect);
		div.innerHTML += " ";

		rect = document.createElement('div');
		rect.className = "sk-rect sk-rect2";
		div.appendChild(rect);
		div.innerHTML += " ";

		rect = document.createElement('div');
		rect.className = "sk-rect sk-rect3";
		div.appendChild(rect);
		div.innerHTML += " ";

		rect = document.createElement('div');
		rect.className = "sk-rect sk-rect4";
		div.appendChild(rect);
		div.innerHTML += " ";

		rect = document.createElement('div');
		rect.className = "sk-rect sk-rect5";
		div.appendChild(rect);

	}

	function formatMoney(amount, decimalCount = 2, decimal = ".", thousands = ",") 
	{
	  try {
	    decimalCount = Math.abs(decimalCount);
	    decimalCount = isNaN(decimalCount) ? 2 : decimalCount;

	    const negativeSign = amount < 0 ? "-" : "";

	    let i = parseInt(amount = Math.abs(Number(amount) || 0).toFixed(decimalCount)).toString();
	    let j = (i.length > 3) ? i.length % 3 : 0;

	    return negativeSign 
	    	+ (j ? i.substr(0, j) + thousands : '') 
	    	+ i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" 
	    	+ thousands) 
	    	+ (decimalCount ? decimal 
	    	+ Math.abs(amount - i).toFixed(decimalCount).slice(2) : ""
	    	);
		  } 
		  catch (e) 
		  {
		    console.log(e)
		  }
	}

	function formatCurrencyInput(input,type=0)
	{
	    $(input).on("keyup input", function( event )
	    {   
	        // When user select text in the document, also abort.
	        var selection = window.getSelection().toString();

	        if (selection !== '' )
	        {
	            return;
	        }
	                
	        // When the arrow keys are pressed, abort.
	        if ( $.inArray( event.keyCode, [38,40,37,39] ) !== -1 )
	        {
	            return;
	        }
	                
	        var $this = $( this );
	                
	        // Get the value.
	        var input = $this.val();
	                
	        var input_length = input.length;

	        //Decimal Not Allowed
	        if(type == 2)
	        {
	        	if(input == 0)
	        	{
	        		input = '';
	        	}
	        	else
	        	{
	        		input = input.replace(/,/g, '');
	        		input = parseFloat(input).toString();
	        	}

	        	input = input.replace(/[^\d]+/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	        }
	        else
	        {
	        	// check for decimal
				if (input.indexOf(".") >= 0) 
				{			  	
				  	// get position of first decimal to prevent multiple decimals from being entered
				    var decimal_pos = input.indexOf(".");

				    // split number by decimal point
				    var left_side = input.substring(0, decimal_pos);//before decimal point 
				    var right_side = input.substring(decimal_pos);//after decimal point

				    if(type == '1')
					{
				    	left_side = left_side.replace(/[^/\d/\.\-]+/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");  
					}
					else
					{
				    	left_side = left_side.replace(/[^/\d/\.]+/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");
					}

				    right_side = right_side.replace(/[^/\d/]+/g, "");  	    
				    
				    // Limit decimal to only 2 digits
				    right_side = right_side.substring(0, 2);

				    input = left_side + "." + right_side;
				} 
				else 
				{
					//if amount is transfer amount, '-' can be entered
					if(type == '1')
					{
				    	input = input.replace(/[^\d\.\-]+/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");   
					}
					else
					{
				    	input = input.replace(/[^\d\.]+/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ",");   
					}
				}
	        }
	        
	        $this.val( function()
	        {
	           //trimming leading zero and dot symbol
	           while(input.substring(0,2) === '00' || input.substring(0,1) === '.')
	           {
	           		input = input.substring(1);
	           }

	           return input;
	        });
	    });
	}

	function getParameterByName(name, url) 
	{
	    if (!url) url = window.location.href;
	    name = name.replace(/[\[\]]/g, '\\$&');
	    var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
	        results = regex.exec(url);
	    if (!results) return null;
	    if (!results[2]) return '';
	    return decodeURIComponent(results[2].replace(/\+/g, ' '));
	}

    function getCurrentDateTime()
    {
    	var toGMT = 9;

        var now = new Date();
		var utc = new Date(now.getTime() + now.getTimezoneOffset() * 60000);
		var now = new Date(utc.getTime() + (toGMT * 60) * 60000);

        var currentHours = padLeft(now.getHours(),2,'0');
        var currentMinutes = padLeft(now.getMinutes(),2,'0');
        var currentSeconds = padLeft(now.getSeconds(),2,'0');

        var day = locale['utils.datetime.day.' + now.getDay()];

        var gmtSymbol = toGMT >= 0 ? '+' : '-';

        var str = now.getFullYear() 
            + '-' + padLeft(now.getMonth() + 1,2,'0')
            + '-' + padLeft(now.getDate(),2,'0') 
            + '&nbsp;' + day
            + '&nbsp;' + currentHours 
            + ':' + currentMinutes 
            + ':' +currentSeconds 
            +'&nbsp;' + 'GMT ' + gmtSymbol + toGMT + ':00';

        return str;
    }

	function padLeft(str, len, prefix)
    {
        return Array(len-String(str).length+1).join(prefix||'0')+str;
    }

    function generateLogData(aryLogFields)
	{
		//aryLogFields - contains id of elements to be put into json
		
	    var obj = {};

	    for (i = 0; i < aryLogFields.length; i++)
	    {
	        var id = aryLogFields[i];

	        obj[id] = $("#" + id).val();
	    }

	    return JSON.stringify(obj);
	}

	function getTodayDB()
	{
	    var toGMT = +9;
	    var now = new Date();
		var utc = new Date(now.getTime() + now.getTimezoneOffset() * 60000);
		var d = new Date(utc.getTime() + (toGMT * 60) * 60000);

	    var month = (1 + d.getMonth()).toString();
	    month = month.length > 1 ? month : '0' + month;

	    var day = d.getDate().toString();
	    day = day.length > 1 ? day : '0' + day;

	    var str =  d.getFullYear() + '-' + month + '-' + day;
	    return str;
	}

	function getTime(datetime)
	{
		var time = datetime.substr(11, 5);

		return time;  
	}

	function getMonth(noOfMonths) 
	{
		var toGMT = +9;
		var now = new Date();
		var utc = new Date(now.getTime() + now.getTimezoneOffset() * 60000);
		var d = new Date(utc.getTime() + (toGMT * 60) * 60000);

	    var month_date;
	    var checkYear = d.getFullYear();
	    var checkMonth = d.getMonth();
	    var checkDate = d.getDate();

	    if (checkMonth == 0) 
	    {
	        checkYear = checkYear - 1;
	        checkMonth = checkMonth - noOfMonths + 12 ;
	    } 
	    else 
	    {
	        checkMonth = checkMonth - noOfMonths; 
	    }

	    var isValidDateResult = isValidDate(checkYear, checkMonth, checkDate);

	    if (isValidDateResult) 
	    {
	        month_date = d.setMonth(d.getMonth() - noOfMonths);
	    } 
	    else 
	    {
		    if (checkMonth == 1) 
		    { 
		        month_date = d.setDate(getDateDay(checkYear, checkMonth, checkDate));
		    } 
		    else 
		    {
		        month_date = d.setDate(d.getDate() - 1);
		    }

	        month_date = d.setMonth(d.getMonth() - noOfMonths);
	    }

	    month_date = new Date(month_date);
	    var the_month_date = ("00" + (month_date.getDate().toString())).slice(-2) + '/' + ("00" + (month_date.getMonth() + 1).toString()).slice(-2) + '/' + month_date.getFullYear();

	    return the_month_date;
	}

	function isValidDate(year, month, day) 
	{	
	    var d = new Date(year, month, day);

	    if (d.getFullYear() == year && d.getMonth() == month && d.getDate() == day) 
	    {
	        return true;
	    }
	    return false;
	}

	function getDateDay(year, month, day) 
	{
	    var lastDayOfTheMonth = new Date(year, month + 1, 0).getDate();
	    if (day > lastDayOfTheMonth) 
	    {
	        return lastDayOfTheMonth;
	    }
	    return day;
	}

	function formattedDate(d)
	{
		var d = new Date(d);
		var year = d.getFullYear();
		var month = ("00" + (d.getMonth() + 1).toString()).slice(-2);
		var day = ("00" + (d.getDate()).toString()).slice(-2);
					
		return year + '-' + month + '-' + day;
	}

	// draw arrow for datepicker
	function drawArrowStart(input) 
	{
		var $input = $(input);
		var widget = $input.datepicker('widget');
		
		setTimeout(function() 
		{
			var inputOffset = $input.offset();
			var widgetOffset = widget.offset();

			var direction = inputOffset.top > widgetOffset.top ? 'down' : 'up';

			$('.ui-datepicker').css('margin-top', direction === 'up' ? '10px' : '-10px');

			$('<div class="datepicker-arrow-start"><div class="inner-arrow"></div></div>').appendTo(widget);
			$('.datepicker-arrow-start')
				.css({
					borderColor: direction === 'up' ? 'transparent transparent #aeaeae' :'#aeaeae transparent transparent' ,
					borderWidth: direction === 'up' ? '0 10px 10px 10px' : '10px 10px 0 10px',
			   		top: direction === 'up' ? '-10px' : null,
			   		bottom: direction === 'up' ? 'auto' : '-10px',
				});

			$('.inner-arrow')
				.css({
					borderColor: direction === 'up' ? 'transparent transparent #f0f0f0' :'#ffffff transparent transparent' ,
					borderWidth: direction === 'up' ? '0 10px 10px 10px' : '10px 10px 0 10px',
			   		top: direction === 'up' ? '1px' : '-11px',
			   		bottom: direction === 'up' ? 'auto' : '-10px',
				});
		}, 10);    
	}

	function drawArrowEnd(input) 
	{
		var $input = $(input);
		var widget = $input.datepicker('widget');
		
		setTimeout(function() 
		{
			var inputOffset = $input.offset();
			var widgetOffset = widget.offset();

			var direction = inputOffset.top > widgetOffset.top ? 'down' : 'up';

			$('.ui-datepicker').css('margin-top', direction === 'up' ? '10px' : '-10px');

			$('<div class="datepicker-arrow-end"><div class="inner-arrow"></div></div>').appendTo(widget);
			$('.datepicker-arrow-end')
				.css({
					borderColor: direction === 'up' ? 'transparent transparent #aeaeae' :'#aeaeae transparent transparent' ,
					borderWidth: direction === 'up' ? '0 10px 10px 10px' : '10px 10px 0 10px',
			   		top: direction === 'up' ? '-10px' : null,
			   		bottom: direction === 'up' ? 'auto' : '-10px',
				});

			$('.inner-arrow')
				.css({
					borderColor: direction === 'up' ? 'transparent transparent #f0f0f0' :'#ffffff transparent transparent' ,
					borderWidth: direction === 'up' ? '0 10px 10px 10px' : '10px 10px 0 10px',
			   		top: direction === 'up' ? '1px' : '-11px',
			   		bottom: direction === 'up' ? 'auto' : '-10px',
				});
		}, 10);    
	}

	//set the date picker option
	function options()
	{
		var language = $('html').attr('lang');

	    if(language == 'kr') // Korean
	    {
	    	$.datepicker.setDefaults(
		    	$.extend({
		    		monthNamesShort: [ "01월", "02월", "03월", "04월", "05월", "06월", "07월", "08월", "09월", "10월", "11월", "12월" ],
		    		dayNamesMin    : [ "일", "월", "화", "수", "목", "금", "토" ],
					yearSuffix: "<span style='color: #33363b;' class='calendar-title-span'>년 </span>",
					showMonthAfterYear: true,
					showOtherMonths: true
		    	}, opts
		    ));
	    }
		else
		{
			$.datepicker.setDefaults(
		    	$.extend({
					showOtherMonths: true
		    	}, opts
		    ));
		}

	   	var opts = {
	        dateFormat: "yy-mm-dd", 
	        altFormat: "yy-mm-dd",
	        maxDate: null, 
	        minDate: null, 
	        changeMonth: true,
	        changeYear: true,
	        selectOtherMonths:true
	    };
	    
	    return opts; 
	}

	function datepickerStart(s_date,e_date,pass_date,set_date,viewType = 1)
	{
	    var opts = options();
		
	    if(set_date == '')
	    {
	        $("#" + s_date).datepicker(
	        $.extend({
	            altField: "#" + pass_date, // the value pass to backend in db format
	            beforeShow: function(input) 
	            {
	                drawArrowStart(input);
	            },
	            onChangeMonthYear: function(a ,b)
	            {
	            	var $input = $(this),
					widget = $(this).datepicker('widget');
			
					drawArrowStart(this);
	            }
	        }, opts));
	    }
	    else
	    {
	        $("#" + s_date).datepicker(
	        $.extend({
	            altField: "#" + pass_date, // the value pass to backend in db format
	            beforeShow: function(input) 
	            {
	                drawArrowStart(input);
	            },
	            onChangeMonthYear: function(a ,b)
	            {
	            	var $input = $(this),
					widget = $(this).datepicker('widget');
			
					drawArrowStart(this);
	            }
	        }, opts)).datepicker("setDate", set_date);
	    }
	}

	function datepickerEnd(s_date,e_date,pass_date,set_date)
	{
	    var opts = options();

	    if(set_date == '')
	    {
	        $("#" + e_date).datepicker(
	        $.extend({
	            altField: "#" + pass_date, // the value pass to backend in db format
	            beforeShow: function(input) 
	            {
	                drawArrowEnd(input);
	            },
	            onChangeMonthYear: function(a ,b)
	            {
	            	var $input = $(this),
					widget = $(this).datepicker('widget');
			
					drawArrowEnd(this);
	            }
	        }, opts));
	    }
	    else
	    {
	        $("#" + e_date).datepicker(
	        $.extend({
	            altField: "#" + pass_date, // the value pass to backend in db format
	            beforeShow: function(input) 
	            {
	                drawArrowEnd(input);
	            },
	            onChangeMonthYear: function(a ,b)
	            {
	            	var $input = $(this),
					widget = $(this).datepicker('widget');
			
					drawArrowEnd(this);
	            }
	        }, opts)).datepicker("setDate", set_date);
	    }
	}

	function getDay(dayNum) 
	{
		var toGMT = +9;
		var now = new Date();
		var utc = new Date(now.getTime() + now.getTimezoneOffset() * 60000);
		var d = new Date(utc.getTime() + (toGMT * 60) * 60000);

	    var day = new Date(d.setDate(d.getDate() - dayNum));
	    var date = day.getFullYear() + '-' + ("00" + (day.getMonth() + 1).toString()).slice(-2) + '-' + ("00" + (day.getDate()).toString()).slice(-2);

	    return date;
	}

	function getMonth(noOfMonths) 
	{
		var toGMT = +9;
		var now = new Date();
		var utc = new Date(now.getTime() + now.getTimezoneOffset() * 60000);
		var d = new Date(utc.getTime() + (toGMT * 60) * 60000);

	    var month_date;
	    var checkYear = d.getFullYear();
	    var checkMonth = d.getMonth();
	    var checkDate = d.getDate();

	    if (checkMonth == 0) 
	    {
	        checkYear = checkYear - 1;
	        checkMonth = checkMonth - noOfMonths + 12 ;
	    } 
	    else 
	    {
	        checkMonth = checkMonth - noOfMonths; 
	    }

	    var isValidDateResult = isValidDate(checkYear, checkMonth, checkDate);

	    if (isValidDateResult) 
	    {
	        month_date = d.setMonth(d.getMonth() - noOfMonths);
	    } 
	    else 
	    {
		    if (checkMonth == 1) 
		    { 
		        month_date = d.setDate(getDateDay(checkYear, checkMonth, checkDate));
		    } 
		    else 
		    {
		        month_date = d.setDate(d.getDate() - 1);
		    }

	        month_date = d.setMonth(d.getMonth() - noOfMonths);
	    }

	    month_date = new Date(month_date);
	    var the_month_date = month_date.getFullYear() + '-' + ("00" + (month_date.getMonth() + 1).toString()).slice(-2) + '-' + ("00" + (month_date.getDate().toString())).slice(-2);

	    return the_month_date;
	}

	function checkAllBox() 
	{
		if($('#checkAll').hasClass('allChecked') == false)
    	{
    		$("input[name='check[]']").each(function(){
		    	this.checked = true; 
		    	$('#checkAll').addClass('allChecked');
		    })
    	}
    	else
    	{
    		$("input[name='check[]']").each(function(){
		    	this.checked = false; 
		    	$('#checkAll').removeClass('allChecked');
		    })
    	}
	}
	
}(this));