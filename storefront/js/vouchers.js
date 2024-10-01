function showVoucherForm()
{
    let voucher_dialog = new JSONFormDialog();
    voucher_dialog.setResponder("VoucherFormResponder");
    voucher_dialog.caption="Kупи Ваучер";

    let dialog = new MessageDialog()
    dialog.initialize();
    dialog.text = "Ще получите Вашият ваучер по куриер";

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