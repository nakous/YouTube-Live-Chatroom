var myapp = angular.module('chatroom', []);
 
// set the configuration
myapp.run(['$rootScope', function($rootScope){
  // the following data is fetched from the JavaScript variables created by wp_localize_script(), and stored in the Angular rootScope
  $rootScope.api = BlogInfo.api;

}]);
 myapp.directive('ngEnter', function () {
    return function (scope, element, attrs) {
        element.bind("keydown keypress", function (event) {
            if(event.which === 13) {
                scope.$apply(function (){
                    scope.$eval(attrs.ngEnter);
                });

                event.preventDefault();
            }
        });
    };
});
// add a controller
myapp.controller('ChatController', ['$scope', '$http', function($scope, $http) {
	$scope.chatsx=[];
	$scope.boxtxt=false;
	$scope.messages="";
	
	$scope.addmessage=function(msg){
		$scope.boxtxt=true;
			var fd = new FormData();
			fd.append('action', 'add_msgtext');
			fd.append('data', msg);
		
			$http.post($scope.api+"/wp-admin/admin-ajax.php",fd, {
					transformRequest: angular.identity,
					headers: {'Content-Type': undefined}
			}).then(function (result) {
				console.log(result.data.items);
				$scope.messages="";
				jQuery(".chatroom-box").scrollTop(jQuery(".chatroom-box").height());
				$scope.boxtxt=false;
			});
			
		}
	

	
		$scope.loadlive = function(){
			var fd = new FormData();
			fd.append('live_chat', 'chat.json');
				$http.post($scope.api,fd, {
						transformRequest: angular.identity,
						headers: {'Content-Type': undefined}
				}).then(function (result) {
						if(typeof  result.error  == 'undefined'){
							$scope.chatsx=result.data.items;
						}else{
							$scope.error=result.error.message;
						}
						jQuery(".chatroom-box").scrollTop(jQuery(".chatroom-box").height());
				});
				
				setTimeout(function(){ $scope.loadlive(); }, 3000);
		 }
	$scope.loadlive();
	
	
}]);

