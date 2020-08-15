$(document).ready(function () {
	checkUserEdit();
	
	$(document).on('input click focus', 'input[name="fio"]', function () {
		let input = this;
		if(this.value.length < 2){
			$(this).css('border','1px solid red').addClass('warn');
		}else{
			$(this).css('border','').removeClass('warn');
			$.ajax({
				url: location.href,
				data: {checkfio:$(this).val()},
				dataType: 'text',
				type: 'POST',
				success: function(data){
					if(data){
						if(data == 0){
							$(input).css('border','').removeClass('warn');
							if($(input).parents('form').find('div#message1').length > 0) $(document).find('div#message1').remove();
						}else if(data == 1){
							$(input).css('border','1px solid red').addClass('warn');
							if($(document).find('div#message1').length < 1) $(input).after("<div class='bg-red color-palette text-center' id='message1'><span>Найдено полное совпадение ФИО!</span></div>");
						}else if(data == 2){
							$(input).css('border','1px solid red').addClass('warn');
							if($(document).find('div#message1').length < 1) $(input).after("<div class='bg-red color-palette text-center' id='message1'><span>Введите Фамилию Имя Отчество!</span></div>");
						}
					}
				}
			});
		}
	});
	
	$(document).on('input click focus', 'input[name="mobile"]', function () {
		let input = this;
		if(this.value.length < 11){
			$(this).css('border','1px solid red').addClass('warn');
		}else{
			$(this).css('border','').removeClass('warn');
		}
	});
	
	$(document).on('input click change', 'select[name="propOblast"]', function () {
		let input = this;
		if(this.value.length < 1){
			$(this).css('border','1px solid red').addClass('warn');
		}else{
			$(this).css('border','').removeClass('warn');
		}
	});
	
	$(document).on('input click change', 'input[name="propCity"]', function () {
		let input = this;
		if(this.value.length < 2){
			$(this).css('border','1px solid red').addClass('warn');
		}else{
			$(this).css('border','').removeClass('warn');
		}
	});
	
	$(document).on('input click change', 'input[name="propHouse"]', function () {
		let input = this;
		if(this.value.length < 1){
			$(this).css('border','1px solid red').addClass('warn');
		}else{
			$(this).css('border','').removeClass('warn');
		}
	});
	
	$(document).on('click', '#addNewClientAdmin', function(e){
		let warn = $(document).find('.warn');
		let arr = [];
		if(warn.length < 1){
			e.preventDefault();
				let info = $(document).find('.info');
				$.each(info, function(i, val){	
					if(val.value != undefined)
						arr[i] = [val.name,val.value];
				});
				if(arr){
					$.ajax({
						url: location.href,
						data: {addClient:arr},
						dataType: 'text',
						type: 'POST',
						success: function(data){
							if(data == 1){
								viewMsg("<div class='callout callout-success'><h4>Клиент успешно зарегистрирован!</h4><p>Переход к списку клиентов через 5 секунд.</p></div>", $(document).find('div.register-box'), 5000, "/law/clients");
							}else{
								alert('Ошибка регистрации!'); return false;
							}
						}
					});
				}

		}else{
			alert('Заполните все поля!'); return false;
		}
	});
	
	$(document).on('click', '#editClientFio', function(e){
		let clientfio = $(document).find('.clientfio');
		let arr = {};
		let form = $(this).parents('form');
		e.preventDefault();
		$.each(clientfio, function(i, val){	
			if(val.value != ''){
				arr[val.name] = val.value;
			}else{
				alert('Заполните все поля!'); return false;
			}
		});
		if(arr){
			$.ajax({
				url: location.href,
				data: {editClient:arr},
				dataType: 'text',
				type: 'POST',
				success: function(data){
					if(data == 1){
						viewMsg("<div class='callout callout-success'><h4>Клиент....</h4><p>Обновляю данные.....ждите....</p></div>", $(form), 3000, location.href);
					}
				}					
			});
		}
	});
	
	$(document).on('click', '#delClient', function(e){
		e.preventDefault();
		let tr = $(this).parents('tr');
		let login = $(this).parents('tr').find('td#login').text();
    if(confirm('Точно удалить клиента? Удалятся все записи входах, оплатах и статистике клиента. Симка станет доступна для повторной регистрации') && login != undefined){
			$.ajax({
				url: location.href,
				data: {delClient:login},
				dataType: 'text',
				type: 'POST',
				success: function(data){
					if(data == 1){
						$(tr).html("<td class='bg-green'>Клиент успешно удален со всеми записями....</td><td class='bg-green'></td><td class='bg-green'></td>");
						setTimeout(function(){$(tr).remove();}, 3000);
					}
				}					
			});
		}
	});
	let trifallsum;
	let trifoplatasum
	$(document).on('click', '#editTarif', function(e){
		e.preventDefault();
		let tr = $(this).parents('tr');
		let triflogin = $(tr).find('td#triflogin');
		let account = $(triflogin).find('span#account');
		let trifall = $(tr).find('td#trifall');
		trifallsum = $(tr).find('td#trifall').text();
		let trifoplata = $(tr).find('td#trifoplata');
		trifoplatasum = $(tr).find('td#trifoplata').text();
		let newtriflogin = "<span id='account'>"+$(account).text()+"</span><a href='#' id='backTarif' class='bg-red' style='float:right;padding:5px'>Назад</a><a href='' id='saveTarif' class='bg-green' style='float:right;padding:5px;margin-right:5px;'>Сохранить</a>";
		$(tr).removeClass('bg-teal');
		$(triflogin).html(newtriflogin);
		$(trifall).html("<input id='trifall' value='"+trifallsum+"'>");
		$(trifoplata).html("<input id='trifoplata' value='"+trifoplatasum+"'>");
		$(document).on('click', '#backTarif', function(e){
			e.preventDefault();
			let odtriflogin = "<span id='account'>"+$(account).text()+"</span><a href='/law/tarifs/edit/"+$(account).text()+"' id='editTarif' class='bg-orange' style='float:right;padding:5px'>Изменить</a>";
			$(triflogin).html(odtriflogin);
			$(trifall).text(trifallsum);
			$(trifoplata).text(trifoplatasum);
		});
	});
	
	$(document).on('click', '#delTarif', function(e){
		e.preventDefault();
		let tr = $(this).parents('tr');
		let login = $(this).parents('tr').find('span#account').text();	
    if(confirm('Точно удалить симку?') && login != undefined){
			$.ajax({
				url: location.href,
				data: {delTarif:login},
				dataType: 'text',
				type: 'POST',
				success: function(data){
					if(data == 1){
						$(tr).html("<td class='bg-green'>Симка успешно удалена....</td><td class='bg-green'></td><td class='bg-green'></td><td class='bg-green'></td><td class='bg-green'></td>");
						setTimeout(function(){$(tr).remove();}, 3000);
					}
				}					
			});
		}
	});
	
	$(document).on('click', '#saveTarif', function(e){
		e.preventDefault();
		let tr = $(this).parents('tr');
		let triflogin = $(tr).find('td#triflogin');
		let account = $(triflogin).find('span#account');
		let trifall = $(tr).find('td#trifall');
		let trifoplata = $(tr).find('td#trifoplata');
		let odtriflogin = "<span id='account'>"+$(account).text()+"</span><a href='/law/tarifs/edit/"+$(account).text()+"' id='editTarif' class='bg-orange' style='float:right;padding:5px'>Изменить</a>";
		let newtrifallsum = $(tr).find('input#trifall').val();
		let newtrifoplata = $(tr).find('input#trifoplata').val();
		if(newtrifallsum != undefined && newtrifoplata != undefined && $(account).text() != undefined){
			$.ajax({
				url: location.href,
				data: {changeTarif:{'account':$(account).text(), 'fullsum':newtrifallsum, 'oplatasum':newtrifoplata}},
				dataType: 'text',
				type: 'POST',
				success: function(data){
					if(data){
						if(data == 1){
							$(tr).addClass('bg-teal');
							$(triflogin).html(odtriflogin);
							$(trifall).text(newtrifallsum);
							$(trifoplata).text(newtrifoplata);
						}else{
							let odtriflogin = "<span id='account'>"+$(account).text()+"</span><a href='/law/tarifs/edit/"+$(account).text()+"' id='editTarif' class='bg-orange' style='float:right;padding:5px'>Изменить</a>";
							$(triflogin).html(odtriflogin);
							$(trifall).text(trifallsum);
							$(trifoplata).text(trifoplatasum);								
						}
					}
				}
			});
		}
	});
	
	$(document).on('click', '#login', function(e){
		e.preventDefault();
		let arr = [];
		let info = $(document).find('.info');
		$.each(info, function(i, val){
			if(val.value != ''){
				arr[i] = [val.name,val.value];
				if($(val).parents('form').find('div#message3').length > 0) $(document).find('div#message3').remove();
			}
		});
		if(arr && arr.length == 2){
			$.ajax({
				url: location.href,
				data: {checkClient:arr},
				dataType: 'text',
				type: 'POST',
				success: function(data){
					if(data == 1){
						viewMsg("<div class='callout callout-success'><h4>Успешный вход!</h4><p>Переход в Ваш кабинет через 3 секунды.</p></div>", $(document).find('div.login-box'), 3000, "/index/mycabinet");
					}else{
						$.each(info, function(i, val){
							if($(document).find('div#message3').length < 2) $(val).after("<div class='bg-red color-palette text-center' id='message3'><span>Не верный логин или пароль!</span></div>");
						});
					}
				}
			});
		}else{
			$.each(info, function(i, val){
				if($(document).find('div#message3').length < 2) $(val).after("<div class='bg-red color-palette text-center' id='message3'><span>Введите данные в оба поля!</span></div>");
			});
		}
	});
	
	// ЗАГРУЗКА КАРТИНКИ
	$(document).on('change', "#import", function(e){
			e.preventDefault();
			let type = $(this).parents('form#getcsv').attr("method");
			let url = $(this).parents('form#getcsv').attr("action");
			let fdata = new FormData($(this).parents('form#getcsv')[0]);
			let csv = document.getElementById("import").files[0];
			let messageBox = document.getElementById("messageImport");
			$(messageBox).html('');
			let fullimgname = document.getElementById("import").files[0].name;
			let ext = fullimgname.substr(fullimgname.lastIndexOf("."));
			if(ext == '.csv'){
				let filename = translit(fullimgname.replace(ext, ''), '_')+ext;
				fdata.append("import",  csv, filename);
				$.ajax({
					url: url,
					data: fdata,
					dataType: "json",
					type: type,
					contentType: false,
					processData: false,
					success: function (data){
						if(data){
							if(data == 5){
								$(messageBox).html('');
								$(messageBox).append("<span class='bg-red' >В файле импорта должно быть 5 столбцов!!</span>");
								$(document).find('button[name="importButton"').removeClass('importButton');
							}else if(data == 4){
								$(messageBox).html('');
								$(messageBox).append("<span class='bg-red' >Ошибка сохранения файла импорта! Проверьте права на папку!!</span>");
								$(document).find('button[name="importButton"').removeClass('importButton');
							}else{
								let vivod = '';
								$.each(data, function(i, val){
									if(val.used == undefined){
										vivod += "<div class='bg-green'>Симка будет добавлена: <input type='hidden' id='addSim' value='"+val+"'>"+val[1]+"</div>";
									}else{
										if(val.used == 1){
											vivod += "<div class='bg-red'>Симка уже есть в базе и номер: "+val[1]+" используется клиентом</div>";
										}else{
											vivod += "<div class='bg-orange'>Симка уже есть в базе: "+val[1]+" ICC: "+val[2]+"</div>";
										}
									}
									$(messageBox).html(''); $(messageBox).append(vivod);
								});
								var sims = $(document).find('input#addSim');
								if(sims.length > 0){
									$(document).find('div.box-footer').css('display','block');
									$(document).find('button[name="importButton"').addClass('importButton');
								}
							}
						}else{
							$(messageBox).append("<span class='bg-red' >Только csv!!</span>");
							$(document).find('div.box-footer').css('display','none');
						}
					}
				});
			}else{
				$(messageBox).append("<span class='bg-red' >Формат файла должен быть csv!</span>");
				$(document).find('div.box-footer').css('display','none');
			}
			return false;
		});
		
		$(document).on('click', "button.importButton", function(e){
			e.preventDefault();
			var sims = $(document).find('input#addSim');
			let messageBox = document.getElementById("messageImport");
			let arr = [];
			$.each(sims, function(i, val){
				arr.push(val.value);
			});
			$(document).find('div.forma').remove();
			$(document).find('div.box-footer').remove();
			$(messageBox).html(''); $(messageBox).append("<div class='alert alert-success alert-dismissible'><h4><i class='icon fa fa-check'></i> Начал запись тарифов!</h4>Подождите, страница обновится сама после записи</div>");
			if(arr.length > 0){
				$(messageBox).html(''); 
				$.ajax({
					url: location.href,
					data: {addTarifs:arr},
					dataType: 'text',
					type: 'POST',
					async: false,
					success: function(data){
						if(data){
							data = JSON.parse(data);
							$.each(data, function(i, arr){
								if(arr[5] == 1)
									$(messageBox).append("<div class='bg-green'>Успешно записана симка "+arr[1]+"</div>");
								else
									$(messageBox).append("<div class='bg-red'>Ошибка записи симки "+arr[1]+". Попробуйте импортировать файл еще раз!</div>");
							});
							$(messageBox).append("<div class='box'><a href='/law/tarifs' class='btn btn-primary'>Перейти к тарифам</a></div>");
						}else{
							$(messageBox).html(''); $(messageBox).append("<div class='callout callout-danger'><h4><i class='icon fa fa-check'></i> Произошла ошибка записи тарифов!</h4> Обратитесь к разработчику</div>");							
						}
					}
				});
			}else{
				$(messageBox).html(''); $(messageBox).append("Нет данных для записи!! Обратитесь к разработчику!");
			}
		});
		
		$('form#sendform').submit( function(e){
			e.preventDefault();
			let info = $(document).find('input.precheck');
			let form = this;
			let arr = {};
			if(info.length == 3 && info != undefined){
				$.each(info, function(i, val){
					arr[$(val).attr('name')] = $(val).val();
				});
				if(arr != {}){
					$.ajax({
						url: location.href,
						data: {addEmptyPayments:arr},
						dataType: 'text',
						type: 'POST',
						async: false,
						success: function(data){
							if(data != -1){
								if(data > 0){
									$(document).find('input#orderid').val(data);
									$(form).unbind('submit').submit();
								}else{
									alert('Произошла ошибка оплаты! Не создался заказ в базе! Обратитесь в поддержку!'); return false;
								}
							}else{
								alert('Произошла ошибка оплаты! Обратитесь в поддержку!'); return false;
							}
						}
					});
				}
			}else{
				alert('Произошла ошибка оплаты! Обратитесь в поддержку!'); return false;
			}
		});

	$(document).on('click', '#delPaymentError', function(e){
		e.preventDefault();
		let tr = $(this).parents('tr');
		let orderid = $(this).parents('tr').find('td#orderid').text();	
    if(confirm('Точно удалить ошибку?') && orderid != undefined){
			$.ajax({
				url: location.href,
				data: {delPaymentError:orderid},
				dataType: 'text',
				type: 'POST',
				success: function(data){
					if(data == 1){
						$(tr).html("<td class='bg-green'>Ошибка успешно удалена....</td><td class='bg-green'></td><td class='bg-green'></td><td class='bg-green'></td>");
						setTimeout(function(){$(tr).remove();}, 3000);
					}
				}					
			});
		}
	});
	
	$(document).on('click', '#delPriorityError', function(e){
		e.preventDefault();
		let tr = $(this).parents('tr');
		let orderid = $(this).parents('tr').find('td#orderid').text();	
    if(confirm('Точно симка пополнена?') && orderid != undefined){
			$.ajax({
				url: location.href,
				data: {delPriorityError:orderid},
				dataType: 'text',
				type: 'POST',
				success: function(data){
					if(data == 1){
						$(tr).html("<td class='bg-green'>Ошибка успешно удалена....</td><td class='bg-green'></td><td class='bg-green'></td><td class='bg-green'></td><td class='bg-green'></td><td class='bg-green'></td>");
						setTimeout(function(){$(tr).remove(); window.location = location.href;}, 3000);
					}
				}					
			});
		}
	});
	
	$(document).on('input click focus', 'input[name="reglogin"]', function () {
		let input = this;
		if(this.value.length >= 8){
			$.ajax({
				url: location.href,
				data: {checkClientlogin:$(this).val()},
				dataType: 'text',
				type: 'POST',
				success: function(data){
					if(data){
						if(data != 'false'){
							$(input).css('border','').removeClass('warn');
							$(document).find('input[name="addClient"]').removeAttr('disabled');
						}else{
							$(input).css('border','1px solid red').addClass('warn');
							$(document).find('input[name="addClient"]').attr('disabled','disabled');
						}
					}
				}
			});
		}else{
			$(this).css('border','1px solid red').addClass('warn');
		}
	});
	
	$(document).on('input click focus', 'textarea[name="address"],textarea[name="passIssued"],textarea[name="passPropiska"],textarea[name="address"]', function () {
		if(this.value.length < 1){
			$(this).css('border','1px solid red').addClass('warn');
		}else{
			$(this).css('border','').removeClass('warn');
		}
	});
	
	$(document).on('input click focus', 'input[name="passSeriya"],input[name="passNumber"],input[name="passKod"],input[name="passBirthdayCity"],input[name="passBirthdayData"]input[name="passBirthdayData"],input[name="eventname"],input[name="eventport"],input[name="agentfio"],input[name="hucksterfio"],input[name="dilerfio"],input[name="managerfio"],input[name="agentpassword"],input[name="managerpassword"],input[name="dilerpassword"],input[name="engineerpassword"],input[name="engineerfio"]', function () {
		let input = this;
		if(this.value.length < 2){
			$(this).css('border','1px solid red').addClass('warn');
		}else{
			$(this).css('border','').removeClass('warn');
		}
	});
	
	$('.useredit').on('input click focus', function () {
		let input = this;
		if(this.value.length < 1){
			$(this).css('border','1px solid red').addClass('warn');
		}else{
			$(this).css('border','').removeClass('warn');
		}
	});
	
	$(document).on('input click focus', 'select[name="prop_oblast"]', function () {
		if(this.value.length == ''){
			$(this).css('border','1px solid red').addClass('warn');
		}else{
			$(this).css('border','').removeClass('warn');
		}
	});
	
	$(document).on('input click focus change', 'input[name="passData"],input[name="passBirthdayData"]', function () {
		let input = this;
		if(this.value.length < 10){
			$(this).css('border','1px solid red').addClass('warn');
		}else{
			$(this).css('border','').removeClass('warn');
		}
	});
	
	$(document).on('input click focus', 'input.password', function () {
		let login = $(document).find('input[name="reglogin"]').val();
		if(this.value != login){
			$(this).css('border','1px solid red').addClass('warn');
		}else{
			$(this).css('border','').removeClass('warn');
		}
	});
	
	$(document).on('click', '#agreed', function(e){
		let form = $(document).find('form#sendform');
		$(form).submit();
	});
	
	$(document).on('change input click', 'input#addpagetitle', function(e){
		$(document).find('input#addpageurl').val(translit(this.value, '_'));
	});
	
	$(document).on('click', '#addPage', function(e){
		e.preventDefault();
		let title = $(document).find('input#addpagetitle').val();
		let url = $(document).find('input#addpageurl').val();
		let content = CKEDITOR.instances.editor1.getData();
		let arr = {};
		if(title != undefined && title != '' && content != undefined && content != '' && url != undefined && url != ''){
			arr.title = title;
			arr.url = url;
			arr.content = content;
			$.ajax({
				url: location.href,
				data: {addPage:arr},
				dataType: 'text',
				type: 'POST',
				success: function(data){
					if(data && data == 1){
						alert('Страница добавлена!'); window.location='/law/pages'; return false;
					}else{
						alert('Ошибка добавления страницы!'); return false;
					}
				}
			});
		}else{
			alert('Все поля обязательные к заполнению!'); return false;
		}
	});
	
	$(document).on('click', '#editPage', function(e){
		e.preventDefault();
		let pageid = $(document).find('input#pageid').val();
		let title = $(document).find('input#pagetitle').val();
		let url = $(document).find('input#pageurl').val();
		let content = CKEDITOR.instances.editor1.getData();
		let arr = {};
		if(pageid != undefined && pageid != '' && title != undefined && title != '' && content != undefined && content != '' && url != undefined && url != ''){
			arr.pageid = pageid;
			arr.title = title;
			arr.url = url;
			arr.content = content;
			$.ajax({
				url: location.href,
				data: {editPage:arr},
				dataType: 'text',
				type: 'POST',
				success: function(data){
					if(data && data == 1){
						alert('Страница обновлена!'); window.location=location.href; return false;
					}else{
						alert('Ошибка Обновления страницы!'); return false;
					}
				}
			});
		}else{
			alert('Все поля обязательные к заполнению!'); return false;
		}
	});
	
	$(document).on('click', '#delPage', function(e){
		e.preventDefault();
		let tr = $(this).parents('tr');
		let pageid = $(this).parents('tr').find('td#pageid').text();
		if(pageid != undefined && pageid != ''){
			if(confirm('Точно удалить страницу?')){
				$.ajax({
					url: location.href,
					data: {delPage:pageid},
					dataType: 'text',
					type: 'POST',
					success: function(data){
						if(data == 1){
							$(tr).html("<td class='bg-green'>Страница успешно удалена....</td><td class='bg-green'></td><td class='bg-green'></td>");
							setTimeout(function(){$(tr).remove();}, 3000);
						}
					}					
				});
			}
		}
	});
	
	$(document).on('click', '#generateDkp', function(e){
		e.preventDefault();
		let div = $(this).parents('div#generateDkpDiv');
		let daterange = $(document).find("input#reservationDkp").val();
		if(div.length == 1 && daterange != 'undefined'){
			$.ajax({
				url: location.href,
				data: {generateDkp:daterange},
				dataType: 'binary',
				type: 'POST',
				xhrFields: {'responseType': 'blob'},
				success: function(data, status, xhr) {
					var blob = new Blob([data], {type: xhr.getResponseHeader('Content-Type')});
					var link = document.createElement('a');
					link.href = window.URL.createObjectURL(blob);
					link.download = 'file.xlsx';
					link.click();
				}				
			});
		}
	});
	
	$(document).on('click', '#editUser', function(e){
		e.preventDefault();
		let data = $(document).find('.userinfo');
		let arr = {};
		let warn = 0;
		if(data.length == 20){
			$.each(data, function(i, val){	
				if(val.value == '' && $(val).hasClass('useredit')){
					warn = 1;
				}
				arr[val.name] = val.value;
			});
			if(warn == 0){
				$.ajax({
					url: location.href,
					data: {editClient:arr},
					dataType: 'text',
					type: 'POST',
					success: function(data){
						if(data && data == 1){
							alert('Клиент обновлен!'); window.location=location.href; return false;
						}else if(data == 2){
							alert('Проверьте корректность указанных дат!'); return false;
						}else{
							alert('Ошибка Обновления клиента!'); return false;
						}
					}
				});
			}else{
				alert('Заполните все поля!'); return false;
			}
		}
	});
});

	function checkUserEdit(){
		let fields = $(document).find('.useredit');
		$.each(fields, function(i, field){
			if($(field).attr('name') != 'prop_oblast'){
				if(field.value.length < 1){
					$(field).css('border','1px solid red').addClass('warn');
				}else{
					$(field).css('border','').removeClass('warn');
				}
			}else{
				if(field.value.length == ''){
					$(field).css('border','1px solid red').addClass('warn');
				}else{
					$(field).css('border','').removeClass('warn');
				}
			}
		});
	}
	
	function viewMsg(msg, block, time, link){
		$(block).html('').append(msg);
		setTimeout('location.replace("'+link+'")',time);
	}
	
	function check(e,value){
		//Check Charater
		var unicode=e.charCode? e.charCode : e.keyCode;
		if (value.indexOf(".") != -1)if( unicode == 46 )return false;
		if (unicode!=8)if((unicode<48||unicode>57)&&unicode!=46)return false;
	}
	
	function checkLength(len,ele){
		var fieldLength = ele.value.length;
		if(fieldLength <= len){
			return fieldLength;
		}else{
			var str = ele.value;
			str = str.substring(0, str.length - 1);
			ele.value = str;
			return fieldLength;
		}
	}
	
// ТРАНСЛИТ 
	function translit(data, space){
		// Символ, на который будут заменяться все спецсимволы
		//var space = '_'; 
		// Берем значение из нужного поля и переводим в нижний регистр
		var text = data.toLowerCase().trim();
		// Массив для транслитерации
		var transl = {
				'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'e', 'ж': 'zh','з': 'z', 'и': 'i', 'й': 'j', 'к': 'k', 'л': 'l', 'м': 'm', 'н': 'n','о': 'o', 'п': 'p', 'р': 'r','с': 's', 'т': 't', 'у': 'u', 'ф': 'f', 'х': 'h','ц': 'c', 'ч': 'ch', 'ш': 'sh', 'щ': 'sh','ъ': space, 'ы': 'y', 'ь': '', 'э': 'e', 'ю': 'yu', 'я': 'ya',
				'&nbsp;': space, '&nbsp;': space, ' ': space, '`': space, '~': space, '@': space,'#': space, '$': space, '%': space, '^': space, '&': space, '*': space,'\=': space, '+': space, '.': space, '\\': space, '|': space, '/': space,',': space,'\'': space, '"': space, ';': space,'?': space, '<': space, '>': space, '№':space			
		}						
		var result = '';
		var curent_sim = '';					
		for(i=0; i < text.length; i++) {
				// Если символ найден в массиве то меняем его
				if(transl[text[i]] != undefined) {
					if(curent_sim != transl[text[i]] || curent_sim != space){
						result += transl[text[i]];
						curent_sim = transl[text[i]];
					}  
				}else if(transl[text[i]] == undefined){
					result += text[i];
					curent_sim = text[i];				
				}else {
					result += space;
					curent_sim = space;
				}                            
		}
		return result.replace(/^-/, '').replace(/-$/, '').replace(/\s+/g, space);
	}	