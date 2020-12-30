function wifiStatus() {
    $.getJSON("wifi.php?action=status", function( data ) {
        $('#wifi_status').html(data.essid+" - "+data.mac+" - "+data.ip);
    }).fail(function() {
        console.log('Error getting wifi status');
    });
}
function wifiScan() {
    $('#scanning').show();
    $.getJSON("wifi.php?action=scan", function( data ) {
        $('#list').empty();
        $.each(data, function(i,v) {
            var sec = ((v.open) ? '*' : '');
            // Closed Lock with Key &#128272;
            // Closed Lock &#128274;
            // Open Lock &#128275;
            $('#list').append(
                "<option value='"+v.bssid+"'>"+v.ssid+sec+" ("+v.bssid+")</option>");
        });
        $('#scanning').hide();
    }).fail(function() {
        console.log('Error scanning wifi');
    });
}
function wifiConnect() {
    $('#connecting').show();
    var essid = $('#list').val();
    var psk = $('#psk').val();
    if(essid) {
        $.post("wifi.php", {action: "connect", ssid: essid, psk: psk}, function (data) {
            alert(data);
            $('#connecting').hide();
        });
    } else {
        alert('ESSID not selected');
        $('#connecting').hide();
    }
}