function showVoucherForm()
{
    let voucher_dialog = new JSONFormDialog();
    voucher_dialog.setResponder("VoucherFormResponder");
    voucher_dialog.setTitle("Kупи Ваучер");

    let dialog = new MessageDialog()
    dialog.setText("Ще получите Вашият ваучер по куриер");

    dialog.buttonAction = function (action) {

        if (action == "confirm") {
            dialog.remove();
            voucher_dialog.show();
        }
        else if (action == "cancel") {
            dialog.remove();
        }
    }

    dialog.show();
}