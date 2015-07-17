define([], function() {
	var	isMobile = window.navigator.userAgent.toLowerCase().indexOf('mobile') != -1,
		sClick = isMobile ? 'tap' : 'click';
		
	return {
		uri: $.baseUrl + 'index.php?route=rest/user',
		btnlogin: $('#btnlogin'),
		btnregister: $('#btnregister'),
		loginPopup: $('#loginPopup'),
		registerPopup: $('#registerPopup'),
		popup: function(popup, text) {
			popup.find("p").text(text);
			popup.popup("open");
		},
		login: function() {
			var $this = this;
			var user = $("input[name='user']").val();
			var pwd = $("input[name='pwd']").val();
			
			if (!user || !pwd) {
				$this.popup($this.loginPopup, '请输入帐号和密码');
				return;
			}

			$.ajax({
                url: $this.uri + '/login',
                type: 'POST',
                dataType: 'json',
				data: {
					user: user,
					password: pwd
				},
                success: function(data) {
                    if (data.result == '0') {
						window.history.back();
						window.location.reload();
                    } else {
						$this.popup($this.loginPopup, '登录失败');
					}
                }
            });
		},
		register: function() {
			var $this = this;
			var telephone = $("input[name='telephone']").val();
			var password = $("input[name='password']").val();
			var password2 = $("input[name='password2']").val();
			var storename = $("input[name='storename']").val();
			var username = $("input[name='username']").val();
			var address = $("input[name='address']").val();
			var invitation = $("input[name='invitation']").val();
			
			if (!telephone || !password || !password2 || !storename || !username || !address || !invitation) {
				$this.popup($this.registerPopup, '请输入完整的注册信息');
				return;
			}
			
			if (password != password2) {
				$this.popup($this.registerPopup, '两次输入的密码不相同');
				return;
			}

			$.ajax({
                url: $this.uri + '/register',
                type: 'POST',
                dataType: 'json',
				data: {
					telephone: telephone,
					password: password,
					storename: storename,
					username: username,
					address: address,
					invitecode: invitation
				},
                success: function(data) {
                    if (data.result == '0') {
						window.history.back(-3);
						window.location.reload();
                    } else {
						$this.popup($this.registerPopup, data.error);
					}
                }
            });
		},
		init: function() {
			var $this = this;
			if ($this.btnlogin.length > 0) {
				$this.btnlogin.off(sClick);
				$this.btnlogin.on(sClick, function() {
					$this.login();
				});
			}
			if ($this.btnregister.length > 0) {
				$this.btnregister.off(sClick);
				$this.btnregister.on(sClick, function() {
					$this.register();
				});
			}
		}
	}
})
