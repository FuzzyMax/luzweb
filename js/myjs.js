//alert("Start");

function chgmerk(id, txt) {
  auth_secrete = localStorage['mypw'] + '//' + localStorage['myuser'];
  url = '/localphp/change.php';
  var posting = $.post(url, { "table": "merk", "id": id, "txt": txt, "auth": auth_secrete });
  posting.done(function (data) {
    $("#ajaxErg").html(data);
  });
}

function chgnina(id, txt) {
  auth_secrete = localStorage['mypw'] + '//' + localStorage['myuser'];
  url = '/localphp/change.php';
  var posting = $.post(url, { "table": "link2", "id": id, "txt": txt, "auth": auth_secrete });
  posting.done(function (data) {
    $("#ajaxErg").html(data);
  });
}

function chgnotiz(id, txt) {
  auth_secrete = localStorage['mypw'] + '//' + localStorage['myuser'];
  url = '/localphp/change.php';
  var posting = $.post(url, { "table": "notiz", "id": id, "txt": txt, "auth": auth_secrete });
  posting.done(function (data) {
    $("#ajaxErg").html(data);
  });
}

function chgzitat(id, txt) {
  auth_secrete = localStorage['mypw'] + '//' + localStorage['myuser'];
  url = '/localphp/change.php';
  var posting = $.post(url, { "table": "zitat", "id": id, "txt": txt, "auth": auth_secrete });
  console.log(txt);
  posting.done(function (data) {
    $("#ajaxErg").html(data);
  });
}

function chgsnippet(id, txt) {
  auth_secrete = localStorage['mypw'] + '//' + localStorage['myuser'];
  url = '/localphp/change.php';
  var posting = $.post(url, { "table": "snippet", "id": id, "txt": txt, "auth": auth_secrete });
  console.log(txt);
  posting.done(function (data) {
    $("#ajaxErg").html(data);
  });
}

function inputKey() {
  alert('HÄ?');
}

function checkKonf() {
  if (!localStorage['mypw'] || !localStorage['myuser']) {
    alert('Konfiguration fehlt !');
  }
}

function showBooks(data) {
  console.log(data.length);
  $("#DBinhalt").html('');

  for (let index = 0; index < data.length; index++) {
    const element = data[index];
    $("#DBinhalt").append(element.b_author + " - " + element.b_titel);
    $("#DBinhalt").append('<br>' + element.b_bemerk + '<br>');
    $("#DBinhalt").append(element.b_isbn);
    $("#DBinhalt").append('<hr/>');
  }
}