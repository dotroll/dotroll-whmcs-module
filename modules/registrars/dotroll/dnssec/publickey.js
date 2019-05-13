function deleteRecord(tag, id) {
	if (tag === 'newtr') {
		$("#" + tag + "_" + id).remove();
	} else {
		$("#" + tag + "_" + id).html('<input type="hidden" name="delrecords[' + id + ']" value="' + id + '" />');
	}
}

function addRecord() {
	id = $('#lastnewrecord').val() * 1 + 1;
	$('#lastnewrecord').val(id);
	html = $('#addtr')[0].outerHTML;
	html = html.replace('addtr', 'newtr_' + id);
	html = html.replace('newrecordflags', 'newrecords[' + id + '][flags]');
	html = html.replace('newrecordprotocol', 'newrecords[' + id + '][protocol]');
	html = html.replace('newrecordalgorithm', 'newrecords[' + id + '][algorithm]');
	html = html.replace('newrecordpublicKey', 'newrecords[' + id + '][publicKey]');
	html = html.replace('null', id);
	html = html.replace(' style="display: none;"', '');
	html = html.replace(' disabled="disabled"', '');
	html = html.replace(' disabled="disabled"', '');
	html = html.replace(' disabled="disabled"', '');
	html = html.replace(' disabled="disabled"', '');
	$("#dnsrecordtbody").append(html);
}
