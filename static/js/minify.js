pimcore.registerNS("pimcore.plugin.minify");

pimcore.plugin.example = Class.create(pimcore.plugin.admin, {
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

var examplePlugin = new pimcore.plugin.minify();

