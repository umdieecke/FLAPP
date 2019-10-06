let FLTemplates = (function () {
    function FLTemplates(givenTemplates) {
        let self = this;
        this.templates = {};
        this.engineReady = false;
        let templatesFinished = 0;
        jQuery(givenTemplates).each(function (index, elem) {
            jQuery.post(elem.tmplPath, {}, function (data) {
                self.templates[elem.tmplName] = {
                    "tmplPath": elem.tmplPath,
                    "tmplRawCode": data
                };
                templatesFinished++;
                if (templatesFinished >= givenTemplates.length) {
                    self.engineReady = true;
                    jQuery(self).trigger("templates-ready");
                }
            });
        });
    }

    FLTemplates.prototype.parse = function (tmplName, objectToInject) {
        if (!this.engineReady) return false;
        let rawTemplateCode = this.templates[tmplName].tmplRawCode;
        jQuery(objectToInject).each(function (index, elem) {
            jQuery.each(elem, function (index, value) {
                if (value == null || value === undefined || value === "null") value = "";
                rawTemplateCode = rawTemplateCode.replaceAll("[@" + index + "]", value);
            });
        });
        return rawTemplateCode;
    };
    return FLTemplates;
})();

/*
 *======= extend String.replace =======
 */

String.prototype.replaceAll = function (stringToFind, stringToReplace) {
    if (stringToFind === stringToReplace) return this;
    let temp = this;
    let index = temp.indexOf(stringToFind);
    while (index !== -1) {
        temp = temp.replace(stringToFind, stringToReplace);
        index = temp.indexOf(stringToFind);
    }
    return temp;
};