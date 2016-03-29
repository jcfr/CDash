CDash.controller('OverviewController',
  function OverviewController($scope, $rootScope, $http, $filter, multisort, filters) {
    $scope.loading = true;
    $http({
      url: 'api/v1/overview.php',
      method: 'GET',
      params: $rootScope.queryString
    }).success(function(cdash) {
      $scope.cdash = cdash;
      // Set title in root scope so the head controller can see it.
      $rootScope['title'] = cdash.title;
    }).finally(function() {
      $scope.loading = false;
    });
});

CDash.directive('linechart', function() {
  return {
    restrict: 'E',
    replace: true,
    scope: {
      data: '=data',
      groupname: '=groupname',
      measurementname: '=measurementname',
      project: '=project',
      anchor: '=anchor',
      sort: '=sort'
    },
    template: '<div class="overview-line-chart"/>',
    link: function(scope, element, attrs) {
      if (scope.groupname) {
        var data = JSON.parse(scope.data);
        if (data.length > 0) {
          element[0].id = scope.groupname + "_" + scope.measurementname + "_chart";
          makeLineChart(element[0].id, data, scope.project, scope.anchor, 0, scope.sort);
        }
      }
    }
  };
});

CDash.directive('bulletchart', function() {
  return {
    restrict: 'E',
    replace: true,
    scope: {
      data: '=data'
    },
    template: '<div class="overview-bullet-chart"><svg></svg></div>',
    link: function(scope, element, attrs) {
      if (scope.data) {
        element[0].id = scope.data.group_name_clean + "_" + scope.data.name + "_bullet";
        var chart_data = JSON.parse(scope.data.chart),
            chart_name = scope.data.group_name + " " + scope.data.nice_name,
            element_name = "#" + element[0].id + " svg";
        makeBulletChart(
          chart_name,
          element_name,
          scope.data.low,
          scope.data.medium,
          scope.data.satisfactory,
          scope.data.current,
          scope.data.previous,
          25);
      }
    }
  };
});