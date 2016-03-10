/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-form-field="{...}">
 */
(function () {
    angular.module('piwikApp').directive('piwikFormField', piwikFormField);

    piwikFormField.$inject = ['piwik'];

    function piwikFormField(piwik){

        return {
            restrict: 'A',
            scope: {
               setting: '=piwikFormField'
            },
            templateUrl: 'plugins/CorePluginsAdmin/angularjs/form-field/form-field.directive.html?cb=' + piwik.cacheBuster,
            compile: function (element, attrs) {

                return function (scope, element, attrs) {
                    if (angular.isArray(scope.setting.defaultValue)) {
                        scope.setting.defaultValue = scope.setting.defaultValue.join(',');
                    }
                };
            }
        };
    }
})();