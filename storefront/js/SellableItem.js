

function SellableItem(sellable_json) {
    this.prodID = sellable_json.prodID;
    //initially selected piID
    this.piID = sellable_json.piID;

    this.sellable = sellable_json;
}


SellableItem.prototype.getSizeValue = function (piID)
{
    return this.sellable.sizes[piID];
}


SellableItem.prototype.pidsByColorID = function (pclrID)
{
    let matching_piIDs = Array();

    Object.entries(this.sellable.colors).forEach(entry => {
        //key = piID
        //value = pclrID
        const [key, value] = entry;

        if (value == pclrID) {
            matching_piIDs.push(key);
        }
    });

    return matching_piIDs;
}
SellableItem.prototype.getSizeValuesByColorID = function(pclrID)
{

    let pids = this.pidsByColorID(pclrID);

    let size_values = Array();

    Object.entries(pids).forEach(entry =>{
        //idx = index
        //value = piID
        const [idx, piID] = entry;
        size_values[piID] = this.getSizeValue(piID);
    });

    return size_values;
}

SellableItem.prototype.getPriceInfosByColorID = function(pclrID)
{
    let pids = this.pidsByColorID(pclrID);

    let price_infos = Array();

    pids.forEach(function(currentValue, index, arr){
        price_infos[currentValue] = this.getPriceInfo(currentValue);
    }, this);

    return price_infos;
}

SellableItem.prototype.getPriceInfo = function(piID)
{
    return this.sellable.prices[piID];
}

SellableItem.prototype.isPromotion = function(piID)
{
    let result = false;

    let priceInfo = this.getPriceInfo(piID);

    if (priceInfo.old_price != priceInfo.sell_price && priceInfo.old_price>0) {
        result = true;
    }

    return result;
}

SellableItem.prototype.getColorID = function (piID)
{
    return this.sellable.colors[piID];
}

SellableItem.prototype.getAttributes = function (piID)
{
    return this.sellable.attributes[piID];
}

SellableItem.prototype.getColorChips = function()
{
    return this.sellable.color_chips;
}

SellableItem.prototype.getColorChip = function (pclrID)
{
    return this.sellable.color_chips[pclrID];
}

SellableItem.prototype.getColorName = function (pclrID)
{
    if (this.sellable.color_names[pclrID]) {
        return this.sellable.color_names[pclrID];
    }
    return null;
}

SellableItem.prototype.getColorCode = function(pclrID)
{
    if (this.sellable.color_codes[pclrID]) {
        return this.sellable.color_codes[pclrID];
    }
    return null;
}

SellableItem.prototype.galleries = function()
{
    return this.sellable.galleries.keys();
}

SellableItem.prototype.haveGalleryItems = function(pclrID)
{
    if (this.sellable.galleries[pclrID]) {
        return true;
    }
    else {
        return false;
    }
}

SellableItem.prototype.galleryItems = function (pclrID)
{
    return this.sellable.galleries[pclrID];
}

