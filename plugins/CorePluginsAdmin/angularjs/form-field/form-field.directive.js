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
                piwikFormField: '=',
                allSettings: '='
            },
            templateUrl: 'plugins/CorePluginsAdmin/angularjs/form-field/form-field.directive.html?cb=' + piwik.cacheBuster,
            compile: function (element, attrs) {

                function evaluateShowIfExpression(scope, field)
                {
                    if (!field.showIf) {
                        return;
                    }

                    var values = {};
                    angular.forEach(scope.allSettings, function (setting) {
                        if (setting.value === '0') {
                            values[setting.name] = 0;
                        } else {
                            values[setting.name] = setting.value;
                        }
                    });

                    field.showField = scope.$eval(field.showIf, values);
                }

                function hasUiControlType(field, uiControlType)
                {
                    return field.uiControlType === uiControlType;
                }

                function isSelectControl(field)
                {
                    return hasUiControlType(field, 'select') || hasUiControlType(field, 'multiselect');
                }

                function hasGroupedValues(availableValues)
                {
                    if (!angular.isObject(availableValues)
                        || angular.isArray(availableValues)) {
                        return false;
                    }

                    var key;
                    for (key in availableValues) {
                        if (Object.prototype.hasOwnProperty.call(availableValues, key)) {
                            if (angular.isObject(availableValues[key])) {
                                return true;
                            } else {
                                return false;
                            }
                        }
                    }

                    return false;
                }

                return function (scope, element, attrs) {
                    var field = scope.piwikFormField;
                    
                    if (angular.isArray(field.defaultValue)) {
                        field.defaultValue = field.defaultValue.join(',');
                    }

                    if (angular.isArray(field.value)) {
                        if (hasUiControlType(field, 'textarea')) {
                            field.value = field.value.join("\n");
                        } else if (hasUiControlType(field, 'text')) {
                            field.value = field.value.join(", ");
                        }
                    }

                    if (isSelectControl(field) && field.availableValues) {
                        var availableValues = field.availableValues;

                        if (!hasGroupedValues(availableValues)) {
                            availableValues = {'': availableValues};
                        }

                        var flatValues = [];
                        angular.forEach(availableValues, function (values, group) {
                            angular.forEach(values, function (value, key) {
                                flatValues.push({group: group, key: key, value: value});
                            });
                        });

                        field.availableValues = flatValues;
                    }

                    field.showField = true;

                    if (field.showIf && scope.allSettings) {
                        evaluateShowIfExpression(scope, field);

                        for (var key in scope.allSettings) {
                            if(scope.allSettings.hasOwnProperty(key)) {
                                scope.$watchCollection('allSettings[' + key + '].value', function (val, oldVal) {
                                    if (val !== oldVal) {
                                        evaluateShowIfExpression(scope, field);
                                    }
                                });
                            }
                        }

                    }

                    scope.formField = field;
                };
            }
        };
    }
})();