/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('PluginSettingsController', PluginSettingsController);

    PluginSettingsController.$inject = ['$scope', 'piwikApi'];

    function PluginSettingsController($scope, piwikApi) {
        // remember to keep controller very simple. Create a service/factory (model) if needed

        var self = this;

        var apiMethod = 'CorePluginsAdmin.getUserSettings';
        debugger;
        if ($scope.mode === 'admin') {
            apiMethod = 'CorePluginsAdmin.getSystemSettings';
        }

        piwikApi.fetch({method: apiMethod}).then(function (settings) {
            self.settingsPerPlugin = settings;
        });

        this.save = function () {
            var apiMethod = 'CorePluginsAdmin.setUserSettings';
            if ($scope.mode === 'admin') {
                apiMethod = 'CorePluginsAdmin.setSystemSettings';
            }

            piwikApi.fetch({method: apiMethod}).then(function (settings) {
                this.settings = settings;
            });
        };
    }
})();