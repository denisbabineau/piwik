/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function () {
    angular.module('piwikApp').controller('SitesManagerSiteController', SitesManagerSiteController);

    SitesManagerSiteController.$inject = ['$scope', '$filter', 'sitesManagerApiHelper', 'sitesManagerTypeModel', 'piwikApi'];

    function SitesManagerSiteController($scope, $filter, sitesManagerApiHelper, sitesManagerTypeModel, piwikApi) {

        var translate = $filter('translate');

        var init = function () {

            initModel();
            initActions();

            $scope.site.isLoading = true;
            sitesManagerTypeModel.fetchTypeById($scope.site.type).then(function (type) {
                $scope.site.isLoading = false;

                if (type) {
                    $scope.currentType = type;
                    $scope.howToSetupUrl = type.howToSetupUrl;
                    $scope.isInternalSetupUrl = '?' === ('' + type.howToSetupUrl).substr(0, 1);

                    if (isSiteNew()) {
                        $scope.measurableSettings = type.settings;
                    }
                } else {
                    $scope.currentType = {name: $scope.site.type};
                }
            });
        };

        var initActions = function () {

            $scope.editSite = editSite;
            $scope.saveSite = saveSite;
            $scope.openDeleteDialog = openDeleteDialog;
            $scope.site['delete'] = deleteSite;
        };

        var initModel = function() {

            if (isSiteNew()) {
                initNewSite();
            }

            $scope.site.removeDialog = {};
        };

        var editSite = function () {
            $scope.site.editMode = true;

            $scope.measurableSettings = [];
            $scope.site.isLoading = true;
            piwikApi.fetch({method: 'SitesManager.getSettings', idSite: $scope.site.idsite}).then(function (settings) {
                $scope.measurableSettings = settings;
                $scope.site.isLoading = false;
            }, function () {
                $scope.site.isLoading = false;
            });
        };

        var saveSite = function() {

            var sendSiteSearchKeywordParams = $scope.site.sitesearch == '1' && !$scope.site.useDefaultSiteSearchParams;
            var sendSearchCategoryParameters = sendSiteSearchKeywordParams && $scope.customVariablesActivated;

            var values = {
                siteName: $scope.site.name,
                timezone: $scope.site.timezone,
                currency: $scope.site.currency,
                type: $scope.site.type,
                settingValues: {}
            };

            var isNewSite = isSiteNew();

            var apiMethod = 'SitesManager.addSite';
            if (!isNewSite) {
                apiMethod = 'SitesManager.updateSite';
                values.idSite = $scope.site.idsite;
            }

            angular.forEach($scope.measurableSettings, function (settings) {
                if (!values['settingValues'][settings.pluginName]) {
                    values['settingValues'][settings.pluginName] = [];
                }

                angular.forEach(settings.settings, function (setting) {
                    var value = setting.value;
                    if (value === false) {
                        value = '0';
                    }
                    values['settingValues'][settings.pluginName].push({
                        name: setting.name,
                        value: value
                    });
                });
            });

            piwikApi.post({method: apiMethod}, values).then(function () {
                $scope.site.editMode = false;

                var UI = require('piwik/UI');
                var notification = new UI.Notification();

                var message = 'Website updated';
                if (isNewSite) {
                    message = 'Website created';
                }

                notification.show(message, {context: 'success'});
                notification.scrollToNotification();
            });
        };

        var isSiteNew = function() {
            return angular.isUndefined($scope.site.idsite);
        };

        var initNewSite = function() {
            $scope.site.editMode = true;
            $scope.site.name = "Name";
            $scope.site.timezone = $scope.globalSettings.defaultTimezone;
            $scope.site.currency = $scope.globalSettings.defaultCurrency;
        };

        var openDeleteDialog = function() {

            $scope.site.removeDialog.title = translate('SitesManager_DeleteConfirm', '"' + $scope.site.name + '" (idSite = ' + $scope.site.idsite + ')');
            $scope.site.removeDialog.show = true;
        };

        var deleteSite = function() {

            var ajaxHandler = new ajaxHelper();

            ajaxHandler.addParams({
                idSite: $scope.site.idsite,
                module: 'API',
                format: 'json',
                method: 'SitesManager.deleteSite'
            }, 'GET');

            ajaxHandler.redirectOnSuccess($scope.redirectParams);
            ajaxHandler.setLoadingElement();
            ajaxHandler.send(true);
        };

        init();
    }
})();