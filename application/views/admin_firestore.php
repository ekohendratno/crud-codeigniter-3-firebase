<!doctype html>
<html lang="en">
<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<!-- Bootstrap CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">

	<title>CRUD Codeigniter 3 dengan Firebase + Bootstrap</title>

</head>
<body>

<div class="container" style="margin-top: 50px;">

	<h4 class="text-center">Firestore</h4><br>
	<h4 class="text-center">CRUD Codeigniter 3 dengan <a href="<?php echo base_url();?>admin/index">Firebase</a>/<a href="<?php echo base_url();?>admin/firestore">Firestore</a> + Bootstrap</h4><br>
	<div class="form-group">
		<input type="text" id="search-name" class="form-control" placeholder="Search by name">
	</div>
	<h5># Tambah Siswa</h5>
	<div class="card card-default">
		<div class="card-body">
			<form id="addStudent" class="form-inline" method="POST" action="">
				<div class="form-group mb-2">
					<label for="nis" class="sr-only">Nomor Induk Siswa</label>
					<input id="nis" type="text" class="form-control" name="nis" placeholder="Nomor Induk Siswa" required autofocus>
				</div>
				<div class="form-group mx-sm-3 mb-2">
					<label for="name" class="sr-only">Nama Siswa</label>
					<input id="name" type="text" class="form-control" name="name" placeholder="Nama Siswa" required autofocus>
				</div>
				<div class="form-group mb-2">
					<label for="age" class="sr-only">Usia</label>
					<input id="age" type="text" class="form-control" name="age" placeholder="Usia" required autofocus>
				</div>
				<button id="submitStudent" type="button" class="btn btn-primary mx-sm-3 mb-2">Tambah</button>
			</form>
		</div>
	</div>

	<br>

	<h5># Data Siswa</h5>
	<div class="table-responsive">
		<table id="employee-table" class="table table-bordered">
			<thead>
			<tr>
				<th>NIS</th>
				<th>Nama Siswa</th>
				<th>Usia</th>
				<th width="180" class="text-center">Action</th>
			</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</div>

	<div class="clearfix">
		<div class="hint-text">Total of <b class="count">0</b> entries</div>
		<ul class="pagination">
			<li class="page-item"><a href="#" id="js-previous" class="page-link">Previous</a></li>
			<li class="page-item"><a href="#" id="js-next" class="page-link">Next</a></li>
		</ul>
	</div>

</div>

<!-- Update Model -->
<form action="" method="POST" class="users-update-record-model form-horizontal">
	<div id="update-modal" data-backdrop="static" data-keyboard="false" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="custom-width-modalLabel"
		 aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" style="width:55%;">
			<div class="modal-content" style="overflow: hidden;">
				<div class="modal-header">
					<h4 class="modal-title" id="custom-width-modalLabel">Update</h4>
					<button type="button" class="close" data-dismiss="modal"
							aria-hidden="true">×
					</button>
				</div>
				<div class="modal-body" id="updateBody">

				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-light" data-dismiss="modal">Close
					</button>
					<button type="button" class="btn btn-success updateStudent">Update
					</button>
				</div>
			</div>
		</div>
	</div>
</form>

<!-- Delete Model -->
<form action="" method="POST" class="users-remove-record-model">
	<div id="remove-modal" data-backdrop="static" data-keyboard="false" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="custom-width-modalLabel"
		 aria-hidden="true" style="display: none;">
		<div class="modal-dialog modal-dialog-centered" style="width:55%;">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title" id="custom-width-modalLabel">Delete</h4>
					<button type="button" class="close remove-data-from-delete-form" data-dismiss="modal" aria-hidden="true">×
					</button>
				</div>
				<div class="modal-body">
					<p>Apakah Anda yakin ingin menghapus data siswa ini?</p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default waves-effect remove-data-from-delete-form" data-dismiss="modal">Close
					</button>
					<button type="button" class="btn btn-danger waves-effect waves-light deleteStudent">Delete
					</button>
				</div>
			</div>
		</div>
	</div>
</form>

<script src="https://code.jquery.com/jquery-3.4.0.min.js"></script>

<!--Firebase Libraries-->
<script src="https://www.gstatic.com/firebasejs/8.6.3/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.6.3/firebase-auth.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.6.3/firebase-firestore.js"></script>

<script>
	// Initialize Firebase
	var firebaseConfig = {
		apiKey: "<?php echo $this->config->item('apiKey') ?>",
		authDomain: "<?php echo $this->config->item('authDomain') ?>",
		databaseURL: "<?php echo $this->config->item('databaseURL') ?>",
		projectId: "<?php echo $this->config->item('projectId') ?>",
		storageBucket: "<?php echo $this->config->item('storageBucket') ?>",
		messagingSenderId: "<?php echo $this->config->item('messagingSenderId') ?>",
		appId: "<?php echo $this->config->item('appId') ?>",
		measurementId: "<?php echo $this->config->item('measurementId') ?>"
	};
	firebase.initializeApp(firebaseConfig);

	var auth = firebase.auth();
	//var storage = firebase.storage();
	var db = firebase.firestore();


	$(document).ready(function () {
		const limit = 2;
		let deleteIDs = [];
		let lastVisibleEmployeeSnapShot = {};
		const employeeRef = db.collection("students");

		// GET TOTAL SIZE
		employeeRef.onSnapshot(snapshot => {
			let size = snapshot.size;
			$('.count').text(size);
		});

		getData();

		function getData() {
			employeeRef.limit(limit).onSnapshot(querySnapshot => {
				var arrObj = [];

				$('#employee-table tbody').html('');
				querySnapshot.forEach((doc) => {
					const data = doc.data();

					//console.log(`${doc.id} => ${data.name}`);

					arrObj.push(data);

					getDataLayout(doc);


				});

				$("#submitStudent").removeClass('disabled');
				lastVisibleEmployeeSnapShot = querySnapshot.docs[querySnapshot.docs.length - 1];

				//console.log("data", arrObj);

			});
		}

		function getDataLayout(document) {
			let item = `<tr>\
							<td>${document.data().nis}</td>
							<td>${document.data().name}</td>
							<td>${document.data().age}</td>
							<td><button data-toggle="modal" data-target="#update-modal" class="btn btn-info updateStudent" data-id="${document.id}">Update</button>
							<button data-toggle="modal" data-target="#remove-modal" class="btn btn-danger removeStudent" data-id="${document.id}">Delete</button></td>
						</tr>`;
			$('#employee-table').append(item);
		}

		//Navigation
		$("#js-previous").on('click', function (e) {
			e.preventDefault();
			$('#employee-table tbody').html('');
			const queryPrevious = employeeRef.endBefore(lastVisibleEmployeeSnapShot).limit(2);

			queryPrevious.get().then(snap => {
				snap.forEach(doc => {
					getDataLayout(doc);
				});
				lastVisibleEmployeeSnapShot = snap.docs[snap.docs.length - 1];
			});
		});

		$('#js-next').on('click', function (e) {
			e.preventDefault();
			if ($(this).closest('.page-item').hasClass('disabled')) {
				return false;
			}
			$('#employee-table tbody').html('');
			const queryNext = employeeRef.startAfter(lastVisibleEmployeeSnapShot).limit(2);

			queryNext.get().then(snap => {
				snap.forEach(doc => {
					getDataLayout(doc);
				});
				lastVisibleEmployeeSnapShot = snap.docs[snap.docs.length - 1];
			});
		});


		// SEARCH
		$("#search-name").keyup(function () {
			let searchString = $("#search-name").val();
			console.log(searchString);
			employeeRef.where("name", ">=", searchString).where("name", "<=", searchString + "\uf8ff").limit(limit).get()
				.then(function (documentSnapshots) {
					$('#employee-table tbody').html('');
					documentSnapshots.docs.forEach(doc => {
						getDataLayout(doc);
					});
				});
		});

		// Add Data
		$('#submitStudent').on('click', function () {
			var values = $("#addStudent").serializeArray();
			var nis = values[0].value;
			var name = values[1].value;
			var age = values[2].value;

			employeeRef.add({
				nis: nis,
				name: name,
				age: age,
			}).then((docRef) => {
				getData();

				$("#addStudent input").val("");
				// menampilkan alert
				alert("Berhasil menambah data");

				console.log("Document written with ID: ", docRef.id);
			}).catch((error) => {
				console.error("Error adding document: ", error);
			});


		});


		// Update Data
		var updateID = 0;
		$('body').on('click', '.updateStudent', function () {
			updateID = $(this).attr('data-id');
			employeeRef.doc(updateID).get().then((doc) => {

				if (doc.exists) {
					console.log("Document data:", doc.data());
					const data = doc.data();

					var updateData = '<div class="form-group">\
                <label for="edit_nis" class="col-md-12 col-form-label">Nomor Induk Siswa</label>\
                <div class="col-md-12">\
                    <input id="edit_nis" type="text" class="form-control" name="edit_nis" value="' + data.nis + '" placeholder="Nomor Induk Siswa" required autofocus>\
                </div>\
            </div>\
            <div class="form-group">\
                <label for="edit_name" class="col-md-12 col-form-label">Nama Lengkap</label>\
                <div class="col-md-12">\
                    <input id="edit_name" type="text" class="form-control" name="edit_name" value="' + data.name + '" placeholder="Nama Siswa" required autofocus>\
                </div>\
            </div>\
            <div class="form-group">\
                <label for="edit_age" class="col-md-12 col-form-label">Usia</label>\
                <div class="col-md-12">\
                    <input id="edit_age" type="text" class="form-control" name="edit_age" value="' + data.age + '" placeholder="Usia" required autofocus>\
                </div>\
            </div>';
					$('#updateBody').html(updateData);

				} else {
					// doc.data() will be undefined in this case
					console.log("No such document!");
				}

			}).catch((error) => {
				console.log("Error getting documents: ", error);
			});

		});

		$('.updateStudent').on('click', function () {
			var values = $(".users-update-record-model").serializeArray();
			var postData = {
				nis: values[0].value,
				name: values[1].value,
				age: values[2].value,
			};

			employeeRef.doc(updateID).set(postData).then(function () {
				getData();
				// menyembunyikan modal
				$("#update-modal").modal('hide');
				// menampilkan alert
				alert("Berhasil mengubah data");
			});

		});


		// Remove Data
		$("body").on('click', '.removeStudent', function () {
			var id = $(this).attr('data-id');
			$('body').find('.users-remove-record-model').append('<input name="id" type="hidden" value="' + id + '">');
		});

		$('.deleteStudent').on('click', function () {
			var values = $(".users-remove-record-model").serializeArray();
			var id = values[0].value;

			employeeRef.doc(id).delete().then(() => {
				console.log("Document successfully deleted!");

				getData();

				$('body').find('.users-remove-record-model').find("input").remove();
				// menyembunyikan modal
				$("#remove-modal").modal('hide');
				// menampilkan alert
				alert("Berhasil menghapus data");

			}).catch((error) => {
				console.error("Error removing document: ", error);
			});

		});
		$('.remove-data-from-delete-form').click(function () {
			$('body').find('.users-remove-record-model').find("input").remove();
		});

	});





	/**
	// Get Data
	firebase.database().ref('students/').on('value', function (snapshot) {
		var value = snapshot.val();
		var htmls = [];
		$.each(value, function (index, value) {
			if (value) {
				htmls.push('<tr>\
                <td>' + value.nis + '</td>\
                <td>' + value.name + '</td>\
                <td>' + value.age + '</td>\
                <td><button data-toggle="modal" data-target="#update-modal" class="btn btn-info updateStudent" data-id="' + index + '">Update</button>\
                <button data-toggle="modal" data-target="#remove-modal" class="btn btn-danger removeStudent" data-id="' + index + '">Delete</button></td>\
            </tr>');
			}
			lastIndex = index;
		});
		$('#tbody').html(htmls);
		$("#submitStudent").removeClass('disabled');
	});

	/**
	// Add Data
	$('#submitStudent').on('click', function () {
		var values = $("#addStudent").serializeArray();
		var nis = values[0].value;
		var name = values[1].value;
		var age = values[2].value;
		var userID = lastIndex + 1;

		firebase.database().ref('students/' + userID).set({
			nis: nis,
			name: name,
			age: age,
		});

		// Reassign lastID value
		lastIndex = userID;
		$("#addStudent input").val("");
		// menampilkan alert
		alert("Berhasil menambah data");
	});

	// Update Data
	var updateID = 0;
	$('body').on('click', '.updateStudent', function () {
		updateID = $(this).attr('data-id');
		firebase.database().ref('students/' + updateID).on('value', function (snapshot) {
			var values = snapshot.val();
			var updateData = '<div class="form-group">\
                <label for="edit_nis" class="col-md-12 col-form-label">Nomor Induk Siswa</label>\
                <div class="col-md-12">\
                    <input id="edit_nis" type="text" class="form-control" name="edit_nis" value="' + values.nis + '" placeholder="Nomor Induk Siswa" required autofocus>\
                </div>\
            </div>\
            <div class="form-group">\
                <label for="edit_name" class="col-md-12 col-form-label">Nama Lengkap</label>\
                <div class="col-md-12">\
                    <input id="edit_name" type="text" class="form-control" name="edit_name" value="' + values.name + '" placeholder="Nama Siswa" required autofocus>\
                </div>\
            </div>\
            <div class="form-group">\
                <label for="edit_age" class="col-md-12 col-form-label">Usia</label>\
                <div class="col-md-12">\
                    <input id="edit_age" type="text" class="form-control" name="edit_age" value="' + values.age + '" placeholder="Usia" required autofocus>\
                </div>\
            </div>';

			$('#updateBody').html(updateData);
		});
	});

	$('.updateStudent').on('click', function () {
		var values = $(".users-update-record-model").serializeArray();
		var postData = {
			nis: values[0].value,
			name: values[1].value,
			age: values[2].value,
		};
		var updates = {};
		updates['/students/' + updateID] = postData;
		firebase.database().ref().update(updates);
		// menyembunyikan modal
		$("#update-modal").modal('hide');
		// menampilkan alert
		alert("Berhasil mengubah data");
	});

	// Remove Data
	$("body").on('click', '.removeStudent', function () {
		var id = $(this).attr('data-id');
		$('body').find('.users-remove-record-model').append('<input name="id" type="hidden" value="' + id + '">');
	});

	$('.deleteStudent').on('click', function () {
		var values = $(".users-remove-record-model").serializeArray();
		var id = values[0].value;
		firebase.database().ref('students/' + id).remove();
		$('body').find('.users-remove-record-model').find("input").remove();
		// menyembunyikan modal
		$("#remove-modal").modal('hide');
		// menampilkan alert
		alert("Berhasil menghapus data");
	});
	$('.remove-data-from-delete-form').click(function () {
		$('body').find('.users-remove-record-model').find("input").remove();
	});*/
</script>

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

</body>
</html>
