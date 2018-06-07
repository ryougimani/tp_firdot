// +----------------------------------------------------------------------
// | Think_firdot
// +----------------------------------------------------------------------
// | 版权所有 2008~2017 上海泛多网络技术有限公司 [ http://www.firdot.com ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.firdot.com
// +----------------------------------------------------------------------

//define(['jquery', 'layui'], function () {

	if (typeof layui !== 'undefined') {
		var form = layui.form,
			layer = layui.layer,
			laydate = layui.laydate;
		if (typeof jQuery === 'undefined') {
			var $ = jQuery = layui.$;
		}
	}

	// jQuery placeholder, fix for IE6,7,8,9
	var JPlaceHolder = new function () {
		this._check = function () {
			return 'placeholder' in document.createElement('input');
		};
		this.init = function () {
			!this._check() && this.fix();
		};
		this.fix = function () {
			$(':input[placeholder]').map(function () {
				var self = $(this), txt = self.attr('placeholder');
				self.wrap($('<div></div>').css({zoom: '1', margin: 'none', border: 'none', padding: 'none', background: 'none', position: 'relative'}));
				var pos = self.position(), h = self.outerHeight(true), paddingleft = self.css('padding-left');
				var holder = $('<span></span>').text(txt).css({position: 'absolute', left: pos.left, top: pos.top, height: h, lineHeight: h + 'px', paddingLeft: paddingleft, color: '#aaa'}).appendTo(self.parent());
				self.on('focusin focusout change keyup', function () {
					self.val() ? holder.hide() : holder.show();
				});
				holder.click(function () {
					self.get(0).focus();
				});
				self.val() && holder.hide();
			});
		};
		this.init();
	};

	// 消息对象
	$.msg = new function () {
		var self = this;
		this.shade = [0.02, '#000'];
		this.dialogIndexs = [];
		// 弹出警告消息框(消息内容, 回调函数)
		this.alert = function (msg, callback) {
			var index = layer.alert(msg, {end: callback, scrollbar: false});
			return this.dialogIndexs.push(index), index;
		};
		// 确认对话框(消息内容, 按钮, 确认回调函数, 取消回调函数)
		this.confirm = function (msg, ok, no) {
			var index = layer.confirm(msg, {title: $confirmLang.title, btn: [$confirmLang.confirm, $confirmLang.cancel]}, function () {
				typeof ok === 'function' && ok.call(this);
			}, function () {
				typeof no === 'function' && no.call(this);
				self.close(index);
			});
			return index;
		};
		// 成功类型消息框(消息内容, 显示时间, 回调函数)
		this.success = function (msg, time, callback) {
			var index = layer.msg(msg, {icon: 1, shade: this.shade, scrollbar: false, end: callback, time: (time || 2) * 1000, shadeClose: true});
			return this.dialogIndexs.push(index), index;
		};
		// 失败类型消息框(消息内容, 显示时间, 回调函数)
		this.error = function (msg, time, callback) {
			var index = layer.msg(msg, {icon: 2, shade: this.shade, scrollbar: false, time: (time || 3) * 1000, end: callback, shadeClose: true});
			return this.dialogIndexs.push(index), index;
		};
		// 状态消息提示(消息内容, 显示时间, 回调函数)
		this.tips = function (msg, time, callback) {
			var index = layer.msg(msg, {time: (time || 3) * 1000, shade: this.shade, end: callback, shadeClose: true});
			return this.dialogIndexs.push(index), index;
		};
		// 加载消息提示(消息内容, 回调函数)
		this.loading = function (msg, callback) {
			var index = msg ? layer.msg(msg, {icon: 16, scrollbar: false, shade: this.shade, time: 0, end: callback}) : layer.load(2, {time: 0, scrollbar: false, shade: this.shade, end: callback});
			return this.dialogIndexs.push(index), index;
		};
		// 关闭消息框
		this.close = function (index) {
			return layer.close(index);
		};
		// 自动处理显示Think返回的Json数据
		this.auto = function (data, time) {
			return (parseInt(data.code) === 1) ? self.success(data.msg, time, function () {
				!!data.url ? (window.location.href = data.url) : $.form.reload();
				for (var i in self.dialogIndexs) {
					layer.close(self.dialogIndexs[i]);
				}
				self.dialogIndexs = [];
			}) : self.error(data.msg, 3, function () {
				!!data.url && (window.location.href = data.url);
			});
		};
	};

	// 表单
	$.form = new function() {
		var self = this;
		// 默认异常提示消息
		this.errMsg = '{status}服务器繁忙，请稍候再试！';
		// 加载后初始化(内容)
		this.reInit = function ($container) {
			$.validate.listen(this), JPlaceHolder.init();
			$container.find('[required]').parent().prevAll('label').addClass('label-required');
		};
		// 以hash打开网页(地址, 标签对象)
		this.href = function (url, obj) {
			if (url !== '#') {
				window.location.href = '#' + $.menu.parseUri(url, obj);
			} else if (obj && obj.getAttribute('data-menu-node')) {
				var node = obj.getAttribute('data-menu-node');
				$('[data-menu-node^="' + node + '-"][data-open!="#"]:first').trigger('click');
			}
			// window.location.href = '#' + $.menu.parseUri(url, obj);
		};
		// 显示到中主内容区(内容, 标签元素)
		this.show = function (html, ele) {
			var $container = $(ele).html(html);
			timReInit.call(this), setTimeout(timReInit, 500), setTimeout(timReInit, 1000);
			function timReInit() {
				$.form.reInit($container);
			}
			if (ele === '.framework-right') {
				$('.framework-right').animate({right: "0px"}, 500);
			}
		};
		this.hide = function (ele) {
			if (ele === '.framework-right') {
				$('.framework-right').animate({right: "-800px"}, 500);
			}
			setTimeout(function () {
				$(ele).empty();
			}, 500);
		};
		// 刷新当前页面
		this.reload = function () {
			window.onhashchange.call(this);
		};
		// 异步加载数据(请求的地址, 参数, 提交的类型, 成功后回调方法, 是否显示加载层, 提示消息, 消息提示时间)
		this.load = function (url, data, type, callback, loading, msg, time) {
			var self = this, dialogIndex = 0;
			(loading !== false) && (dialogIndex = $.msg.loading(msg));
			(typeof Pace === 'object') && Pace.restart();
			$.ajax({
				type: type || 'GET', url: $.menu.parseUri(url), data: data || {},
				statusCode: {
					404: function () {
						$.msg.close(dialogIndex);
						$.msg.tips(self.errMsg.replace('{status}', 'E404 - '));
					},
					500: function () {
						$.msg.close(dialogIndex);
						$.msg.tips(self.errMsg.replace('{status}', 'E500 - '));
					}
				},
				error: function (XMLHttpRequest, textStatus, errorThrown) {
					$.msg.close(dialogIndex);
					$.msg.tips(self.errMsg.replace('{status}', 'E' + textStatus + ' - '));
				},
				success: function (res) {
					$.msg.close(dialogIndex);
					if (typeof callback === 'function' && callback.call(self, res) === false) {
						return false;
					}
					if (typeof (res) === 'object') {
						return $.msg.auto(res, time || res.wait || undefined);
					}
					self.show(res, '.framework-body');
				}
			});
		};
		// 打开一个iFrame窗口(请求的地址, 标题, 最大最小化)
		this.iframe = function (url, title, maxmin) {
			return layer.open({title: title || '窗口', type: 2, area: ['800px', '530px'], fix: true, maxmin: maxmin || false, content: url});
		};
		// 加载HTML到弹出层(请求的地址, 参数, 标题, 回调函数, 是否显示加载, 提示消息)
		this.modal = function (url, data, title, callback, loading, msg) {
			this.load(url, data, 'GET', function (res) {
				if (typeof (res) === 'object') {
					return $.msg.auto(res);
				}
				var layerIndex = layer.open({
					type: 1, btn: false, area: "800px", content: res, title: title || '', success: function (dom, index) {
						$(dom).find('[data-close]').off('click').on('click', function () {
							if ($(this).attr('data-confirm')) {
								var confirmIndex = $.msg.confirm($(this).attr('data-confirm'), function () {
									layer.close(confirmIndex), layer.close(index);
								});
								return false;
							}
							layer.close(index);
						});
						$.form.reInit($(dom));
					}
				});
				$.msg.dialogIndexs.push(layerIndex);
				return (typeof callback === 'function') && callback.call(this);
			}, loading, msg);
		};
		// 加载HTML到左侧位置(请求的地址, 参数, 回调函数, 是否显示加载, 提示消息)
		this.right = function (url, data, callback, loading, msg) {
			this.load(url, data, 'GET', function (res) {
				if (typeof (res) === 'object') {
					return $.msg.auto(res);
				}
				self.show(res, '.framework-right');
				return (typeof callback === 'function') && callback.call(this);
			}, loading, msg);
		};
		// 加载HTML到目标位置(请求的地址, 参数, 回调函数, 是否显示加载, 提示消息)
		this.open = function (url, data, callback, loading, msg) {
			this.load(url, data, 'GET', function (res) {
				if (typeof (res) === 'object') {
					return $.msg.auto(res);
				}
				self.show(res, '.framework-body');
			}, loading, msg);
		};
	};

	// 表单验证(表单, 回调方法, 参数)
	$.validate = function (form, callback, options) {
		return (new function () {
			var self = this;
			this.tags = 'input,select,textarea';
			this.checkEvent = {change: true, blur: true, keyup: false};
			// 去除字符串两头的空格(字符串)
			this.trim = function (str) {
				return str.replace(/(^\s*)|(\s*$)/g, '');
			};
			// 是否可见(标签元素)
			this.isVisible = function (ele) {
				return $(ele).is(':visible');
			};
			// 检测属性是否有定义(标签元素, 属性名称)
			this.hasProp = function (ele, prop) {
				if (typeof prop !== "string") return false;
				var attrProp = ele.getAttribute(prop);
				return (typeof attrProp !== 'undefined' && attrProp !== null && attrProp !== false);
			};
			// 检测是否为空(标签元素, 默认值)
			this.isEmpty = function (ele, value) {
				var trimValue = this.trim(ele.value);
				value = value || ele.getAttribute('placeholder');
				return (trimValue === "" || trimValue === value);
			};
			// 正则验证(标签元素, 正则, 修饰符)
			this.isRegex = function (ele, regex, params) {
				var inputValue = ele.value, dealValue = this.trim(inputValue);
				regex = regex || ele.getAttribute('pattern');
				if (dealValue === "" || !regex) return true;
				if (dealValue !== inputValue)
					(ele.tagName.toLowerCase() !== "textarea") ? (ele.value = dealValue) : (ele.innerHTML = dealValue);
				return new RegExp(regex, params || 'i').test(dealValue);
			};
			// 验证标志(标签元素)
			this.remind = function (input) {
				return this.isVisible(input) ? this.showError(input, input.getAttribute('title') || '') : false;
			};
			// 检测表单单元(标签元素)
			this.checkInput = function (input) {
				var type = (input.getAttribute("type") + "").replace(/\W+$/, "").toLowerCase();
				var tag = input.tagName.toLowerCase(), isRequired = this.hasProp(input, "required");
				if (this.hasProp(input, 'data-auto-none') || input.disabled || type === 'submit' || type === 'reset' || type === 'file' || type === 'image' || !this.isVisible(input)) {
					return;
				}
				var allPass = true;
				if (type === "radio" && isRequired) {
					var radioPass = false, eleRadios = input.name ? $("input[type='radio'][name='" + input.name + "']") : $(input);
					eleRadios.each(function () {
						(radioPass === false && $(this).is("[checked]")) && (radioPass = true);
					});
					if (radioPass === false) {
						allPass = this.remind(eleRadios.get(0), type, tag);
					} else {
						this.hideError(input);
					}
				} else if (type === "checkbox" && isRequired && !$(input).is("[checked]")) {
					allPass = this.remind(input, type, tag);
				} else if (tag === "select" && isRequired && !input.value) {
					allPass = this.remind(input, type, tag);
				} else if ((isRequired && this.isEmpty(input)) || !(allPass = this.isRegex(input))) {
					allPass ? this.remind(input, type, "empty") : this.remind(input, type, tag);
					allPass = false;
				} else {
					this.hideError(input);
				}
				return allPass;
			};
			// 检侧所的表单元素(标签元素, 参数)
			this.isAllpass = function (elements, options) {
				if (!elements) {
					return true;
				}
				var allPass = true, self = this, params = options || {};
				if (elements.size && elements.size() === 1 && elements.get(0).tagName.toLowerCase() === "form") {
					elements = $(elements).find(self.tags);
				} else if (elements.tagName && elements.tagName.toLowerCase() === "form") {
					elements = $(elements).find(self.tags);
				}
				elements.each(function () {
					if (self.checkInput(this, params) === false) {
						return $(this).focus(), (allPass = false);
					}
				});
				return allPass;
			};
			// 错误消息显示(标签元素, 内容)
			this.showError = function (ele, content) {
				$(ele).addClass('validate-error'), this.insertError(ele);
				$($(ele).data('input-info')).addClass('fadeInRight animated').css({width: 'auto'}).html(content);
			};
			// 错误消息隐藏(标签元素)
			this.hideError = function (ele) {
				$(ele).removeClass('validate-error'), this.insertError(ele);
				$($(ele).data('input-info')).removeClass('fadeInRight').css({width: '30px'}).html('');
			};
			// 错误消息标签插入(标签元素)
			this.insertError = function (ele) {
				var $html = $('<span style="-webkit-animation-duration:.2s;animation-duration:.2s;padding-right:20px;color:#a94442;position:absolute;right:0;font-size:12px;z-index:2;display:block;width:34px;text-align:center;pointer-events:none"></span>');
				$html.css({top: $(ele).position().top + 'px', paddingBottom: $(ele).css('paddingBottom'), lineHeight: $(ele).css('height')});
				$(ele).data('input-info') || $(ele).data('input-info', $html.insertAfter(ele));
			};
			// 表单验证入口(表单, 回调函数, 参数)
			this.check = function (form, callback, options) {
				var params = $.extend({}, options || {});
				$(form).attr("novalidate", "novalidate");
				$(form).find(self.tags).map(function () {
					for (var i in self.checkEvent) {
						if (self.checkEvent[i] === true) {
							$(this).off(i, func).on(i, func);
						}
					}
					function func() {
						self.checkInput(this);
					}
				});
				$(form).bind("submit", function (event) {
					if (self.isAllpass($(this).find(self.tags), params) && typeof callback === 'function') {
						// if (typeof window['_ckeditor_callback'] === 'function') {
						// 	window['_ckeditor_callback'].call(this);
						// }
						if (typeof CKEDITOR === 'object' && typeof CKEDITOR.instances === 'object') {
							for (var instance in CKEDITOR.instances) {
								CKEDITOR.instances[instance].updateElement();
							}
						}
						callback.call(this, $(form).serialize());
					}
					return event.preventDefault(), false;
				});
				return $(form).data('validate', this);
			};
		}).check(form, callback, options);
	};

	// 自动监听规则内表单
	$.validate.listen = function () {
		$('form[data-auto]').map(function () {
			if ($(this).attr('data-listen') !== 'true') {
				var callbackName = $(this).attr('data-callback');
				$(this).attr('data-listen', 'true').validate(function (data) {
					var method = this.getAttribute('method') || 'POST';
					var tips = this.getAttribute('data-tips') || undefined;
					var url = this.getAttribute('action') || window.location.href;
					var callback = window[callbackName || '_default_callback'] || undefined;
					var time = this.getAttribute('data-time') || undefined;
					$.form.load(url, data, method, callback, true, tips, time);
				});
				$(this).find('[data-form-loaded]').map(function () {
					$(this).html(this.getAttribute('data-form-loaded') || this.innerHTML);
					$(this).removeAttr('data-form-loaded').removeClass('layui-disabled');
				});
			}
		});
	};

	// 注册到JQ验证方法(回调方法, 参数)
	$.fn.validate = function (callback, options) {
		return $.validate(this, callback, options);
	};

	// 后台菜单辅助插件
	$.menu = new function () {
		// 获取有效的URI
		this.getUri = function (uri) {
			uri = uri || window.location.href;
			uri = (uri.indexOf(window.location.host) > -1 ? uri.split(window.location.host)[1] : uri).split('?')[0];
			return (uri.indexOf('#') !== -1 ? uri.split('#')[1] : uri);
		};
		// 查询对应URI的菜单
		this.queryNode = function (url) {
			var node = location.href.replace(/.*spm=([\d\-m]+).*/ig, '$1');
			if (!/^m\-/.test(node)) {
				var $menu = $('[data-menu-node][data-open*="' + url.replace(/\.html$/ig, '') + '"]');
				return $menu.size() ? $menu.get(0).getAttribute('data-menu-node') : '';
			}
			return node;
		};
		// URL转URI
		this.parseUri = function (uri, obj) {
			var params = {};
			if (uri.indexOf('?') > -1) {
				var queryParams = uri.split('?')[1].split('&');
				for (var i in queryParams) {
					if (queryParams[i].indexOf('=') > -1) {
						var temp = queryParams[i].split('=');
						try {
							params[temp[0]] = window.decodeURIComponent(window.decodeURIComponent(temp[1].replace(/%2B/ig, ' ')));
						} catch (e) {
							console.log([e, uri, queryParams, temp]);
						}
					}
				}
			}
			uri = this.getUri(uri);
			params.spm = obj && obj.getAttribute('data-menu-node') || this.queryNode(uri);
			if (!params.token) {
				var token = window.location.href.replace(/.*token=(\w+).*/ig, '$1');
				(/^\w{16}$/.test(token)) && (params.token = token);
			}
			delete params[""];
			var query = '?' + $.param(params);
			return uri + (query !== '?' ? query : '');
		};
		// 初始化
		this.listen = function () {
			// 点击顶部菜单切换边侧菜单
			var $menu_tab = $('[data-menu-tab]').on('click', function () {
				$menu_tab.not($(this).addClass('active')).removeClass('active');
				var data_menu = $(this).attr('data-menu-tab'), $sidebar = $('[data-menu-sidebar=' + data_menu + ']').removeClass('hide');
				$('[data-menu-sidebar]').not($sidebar).addClass('hide');
				$sidebar.find('[data-open]:first').trigger('click');
			});
			// 展开收起子集菜单
			$('[data-menu-header]').on('click', function () {
				if ($('.framework-box').hasClass('framework-sidebar-full')) {
					var $sub = $(this).next('[data-menu-content]'), content = $sub.attr('data-menu-content') || false;
					content && $.cookie(content, $(this).parent().toggleClass('open').hasClass('open') ? 2 : 1);
					$(this).parent().hasClass('open') ? $sub.show() : $sub.hide();
				}
			});
			// 边侧底层菜单点击
			$('[data-menu-item] a').on('click', function () {
				$(this).parents('.sidebar-menu').addClass('open').find('[data-menu-content]').show();
				$('[data-menu-item]').not($(this).parent().addClass('active')).removeClass('active');
			});
			// // 边侧菜单
			// $('[data-menu-content]').map(function () {
			// 	var node = this.getAttribute('data-menu-node') || false;
			// 	node && (parseInt($.cookie(node) || 2) === 2) && $(this).show().parent().addClass('open');
			// });
			// // mini模式tips显示
			// var menu_tips;
			// $('[data-menu-item]').on('mouseenter', function () {
			// 	if ($('.framework-box').hasClass('framework-sidebar-mini')) {
			// 		menu_tips = layer.tips($(this).text(), $(this), {tips:4, skin: 'sidebar-menu', time:0});
			// 	}
			// }).on('mouseleave', function(){
			// 	if ($('.framework-box').hasClass('framework-sidebar-mini')) {
			// 		layer.close(menu_tips);
			// 	}
			// });
			/* Mini 菜单模式 Tips 显示 */
			$('body').on('mouseenter mouseleave', '.framework-sidebar-mini .menu-item', function (e) {
				//alert(123);
				$(this).tooltip({
					template: '<div class="console-sidebar-tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
					title: $(this).text(), placement: 'right', container: 'body'
				}).tooltip('show'), (e.type === 'mouseleave') && $(this).tooltip('destroy');
			});
			// 边侧栏状态切换
			var $sidebar_menu = $('.sidebar-fold').on('click', function () {
				var $body = $('.framework-box').toggleClass('framework-sidebar-mini framework-sidebar-full');
				//$.cookie('menu-style', $body.hasClass('framework-sidebar-mini') ? 'mini' : 'full');
			});
			// ($.cookie('menu-style') === 'mini') && $sidebar_menu.trigger('click');
			// hash事件
			window.onhashchange = function () {
				// 获取当前内容页面地址和菜单
				var hash = (window.location.hash || '').substring(1), node = hash.replace(/.*spm=([\d\-m]+).*!/ig, "$1");
				if (!/^m\-[\d\-]+$/.test(node)) {
					node = $.menu.queryNode($.menu.getUri()) || '';
				}
				// 默认内容页面
				if (hash.length < 1 || node.length < 1) {
					return $('[data-header-menu]:first').trigger('click');
				}
				// 当前内容页面对应的菜单
				var parent_node = [node.split('-')[0], node.split('-')[1]].join('-');
				$('[data-header-menu]').not($('[data-menu-tab="' + parent_node + '"]').addClass('active')).removeClass('active');
				//$('[data-header-menu]').not($('[data-menu-tab="' + parent_node + '"]').addClass('layui-this')).removeClass('layui-this');
				var $menu = $('[data-menu-node="' + node + '"]').eq(0);
				if ($menu.size() > 0) {
					$menu.parents('.sidebar-menu').addClass('open'), $menu.parents('.sidebar-trans').removeClass('hide').show();
					var $li = $menu.parent('li').addClass('active');
					$li.parents('.framework-sidebar').find('li.active').not($li).removeClass('active');
					$menu.parents('[data-menu-sidebar]').removeClass('hide').siblings('[data-menu-sidebar]').addClass('hide');
					if (/^m\-\d+$/i.test(node)) {
						$('.framework-sidebar').addClass('hide'), $menu.addClass('active');
						$('.framework-body').css('left', 0).addClass('framework-sidebar-full');
					} else {
						$('.framework-sidebar').removeClass('hide');
						//$('.framework-body').removeAttr('style').addClass('framework-sidebar-full');
					}
				} else {
					$('.framework-sidebar').hide();
					//$('.framework-body').removeClass('framework-sidebar-full');
				}
				$.form.open(hash);
			};
			// 初始化
			window.onhashchange.call(this);
		};
	};

//});