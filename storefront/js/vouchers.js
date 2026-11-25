function showVoucherForm()
{
    let voucher_dialog = new JSONFormDialog();
    voucher_dialog.setResponder("VoucherFormResponder");
    voucher_dialog.setTitle(tr("Buy a voucher"));

    let dialog = new MessageDialog()
    dialog.setText(tr("You will receive your voucher by courier"));

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