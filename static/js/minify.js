pimcore.registerNS("pimcore.plugin.minify");

pimcore.plugin.minify = Class.create(pimcore.plugin.admin, {
    getClassName: function() {
        return "pimcore.plugin.minify";
    },

    initialize: function() {
        pimcore.plugin.broker.registerPlugin(this);
    },
 
    pimcoreReady: function (params,broker){
        // alert("Example Ready!");
    }
});

var minifyPlugin = new pimcore.plugin.minify();

