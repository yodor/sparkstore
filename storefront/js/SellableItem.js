class SellableItem {
    /**
     *
     * @param sellable_json {object} json serialized SellableItem
     */
    constructor(sellable_json) {
        this.prodID = sellable_json.prodID;
        //initially selected piID
        this.piID = sellable_json.piID;

        this.sellable = sellable_json;
    }

    getSizeValue(piID) {
        return this.sellable.sizes[piID];
    }

    pidsByColorID(pclrID)
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

    getSizeValuesByColorID(pclrID)
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

    getPriceInfosByColorID(pclrID)
    {
        let pids = this.pidsByColorID(pclrID);

        let price_infos = Array();

        pids.forEach(function(currentValue, index, arr){
            price_infos[currentValue] = this.getPriceInfo(currentValue);
        }, this);

        return price_infos;
    }

    getPriceInfo(piID)
    {
        return this.sellable.prices[piID];
    }

    isPromotion(piID)
    {
        let result = false;

        let priceInfo = this.getPriceInfo(piID);

        if (priceInfo.old_price != priceInfo.sell_price && priceInfo.old_price>0) {
            result = true;
        }

        return result;
    }

    getColorID(piID)
    {
        return this.sellable.colors[piID];
    }

    getAttributes(piID)
    {
        return this.sellable.attributes[piID];
    }

    getColorChips()
    {
        return this.sellable.color_chips;
    }

    getColorChip(pclrID)
    {
        return this.sellable.color_chips[pclrID];
    }

    getColorName(pclrID)
    {
        if (this.sellable.color_names[pclrID]) {
            return this.sellable.color_names[pclrID];
        }
        return null;
    }

    getColorCode(pclrID)
    {
        if (this.sellable.color_codes[pclrID]) {
            return this.sellable.color_codes[pclrID];
        }
        return null;
    }

    galleries()
    {
        return this.sellable.galleries.keys();
    }

    haveGalleryItems(pclrID)
    {
        if (this.sellable.galleries[pclrID]) {
            return true;
        }
        else {
            return false;
        }
    }

    galleryItems(pclrID)
    {
        return this.sellable.galleries[pclrID];
    }
}







