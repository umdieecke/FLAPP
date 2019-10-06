/* http://prismjs.com/download.html?themes=prism&languages=markup+css+css-extras+clike+javascript+php+coffeescript+scss+sql */
self = typeof window != "undefined" ? window : typeof WorkerGlobalScope != "undefined" && self instanceof WorkerGlobalScope ? self : {};
var Prism = function () {
	var e = /\blang(?:uage)?-(?!\*)(\w+)\b/i, t = self.Prism = {
		util: {
			encode: function (e) {
				return e instanceof n ? new n(e.type, t.util.encode(e.content), e.alias) : t.util.type(e) === "Array" ? e.map(t.util.encode) : e.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/\u00a0/g, " ")
			}, type: function (e) {
				return Object.prototype.toString.call(e).match(/\[object (\w+)\]/)[1]
			}, clone: function (e) {
				var n = t.util.type(e);
				switch (n) {
					case"Object":
						var r = {};
						for (var i in e)e.hasOwnProperty(i) && (r[i] = t.util.clone(e[i]));
						return r;
					case"Array":
						return e.slice()
				}
				return e
			}
		}, languages: {
			extend: function (e, n) {
				var r = t.util.clone(t.languages[e]);
				for (var i in n)r[i] = n[i];
				return r
			}, insertBefore: function (e, n, r, i) {
				i = i || t.languages;
				var s = i[e];
				if (arguments.length == 2) {
					r = arguments[1];
					for (var o in r)r.hasOwnProperty(o) && (s[o] = r[o]);
					return s
				}
				var u = {};
				for (var a in s)if (s.hasOwnProperty(a)) {
					if (a == n)for (var o in r)r.hasOwnProperty(o) && (u[o] = r[o]);
					u[a] = s[a]
				}
				t.languages.DFS(t.languages, function (t, n) {
					n === i[e] && t != e && (this[t] = u)
				});
				return i[e] = u
			}, DFS: function (e, n, r) {
				for (var i in e)if (e.hasOwnProperty(i)) {
					n.call(e, i, e[i], r || i);
					t.util.type(e[i]) === "Object" ? t.languages.DFS(e[i], n) : t.util.type(e[i]) === "Array" && t.languages.DFS(e[i], n, i)
				}
			}
		}, highlightAll: function (e, n) {
			var r = document.querySelectorAll('code[class*="language-"], [class*="language-"] code, code[class*="lang-"], [class*="lang-"] code');
			for (var i = 0, s; s = r[i++];)t.highlightElement(s, e === !0, n)
		}, highlightElement: function (r, i, s) {
			var o, u, a = r;
			while (a && !e.test(a.className))a = a.parentNode;
			if (a) {
				o = (a.className.match(e) || [, ""])[1];
				u = t.languages[o]
			}
			if (!u)return;
			r.className = r.className.replace(e, "").replace(/\s+/g, " ") + " language-" + o;
			a = r.parentNode;
			/pre/i.test(a.nodeName) && (a.className = a.className.replace(e, "").replace(/\s+/g, " ") + " language-" + o);
			var f = r.textContent;
			if (!f)return;
			var l = {element: r, language: o, grammar: u, code: f};
			t.hooks.run("before-highlight", l);
			if (i && self.Worker) {
				var c = new Worker(t.filename);
				c.onmessage = function (e) {
					l.highlightedCode = n.stringify(JSON.parse(e.data), o);
					t.hooks.run("before-insert", l);
					l.element.innerHTML = l.highlightedCode;
					s && s.call(l.element);
					t.hooks.run("after-highlight", l)
				};
				c.postMessage(JSON.stringify({language: l.language, code: l.code}))
			} else {
				l.highlightedCode = t.highlight(l.code, l.grammar, l.language);
				t.hooks.run("before-insert", l);
				l.element.innerHTML = l.highlightedCode;
				s && s.call(r);
				t.hooks.run("after-highlight", l)
			}
		}, highlight: function (e, r, i) {
			var s = t.tokenize(e, r);
			return n.stringify(t.util.encode(s), i)
		}, tokenize: function (e, n, r) {
			var i = t.Token, s = [e], o = n.rest;
			if (o) {
				for (var u in o)n[u] = o[u];
				delete n.rest
			}
			e:for (var u in n) {
				if (!n.hasOwnProperty(u) || !n[u])continue;
				var a = n[u];
				a = t.util.type(a) === "Array" ? a : [a];
				for (var f = 0; f < a.length; ++f) {
					var l = a[f], c = l.inside, h = !!l.lookbehind, p = 0, d = l.alias;
					l = l.pattern || l;
					for (var v = 0; v < s.length; v++) {
						var m = s[v];
						if (s.length > e.length)break e;
						if (m instanceof i)continue;
						l.lastIndex = 0;
						var g = l.exec(m);
						if (g) {
							h && (p = g[1].length);
							var y = g.index - 1 + p, g = g[0].slice(p), b = g.length, w = y + b, E = m.slice(0, y + 1), S = m.slice(w + 1), x = [v, 1];
							E && x.push(E);
							var T = new i(u, c ? t.tokenize(g, c) : g, d);
							x.push(T);
							S && x.push(S);
							Array.prototype.splice.apply(s, x)
						}
					}
				}
			}
			return s
		}, hooks: {
			all: {}, add: function (e, n) {
				var r = t.hooks.all;
				r[e] = r[e] || [];
				r[e].push(n)
			}, run: function (e, n) {
				var r = t.hooks.all[e];
				if (!r || !r.length)return;
				for (var i = 0, s; s = r[i++];)s(n)
			}
		}
	}, n = t.Token = function (e, t, n) {
		this.type = e;
		this.content = t;
		this.alias = n
	};
	n.stringify = function (e, r, i) {
		if (typeof e == "string")return e;
		if (Object.prototype.toString.call(e) == "[object Array]")return e.map(function (t) {
			return n.stringify(t, r, e)
		}).join("");
		var s = {type: e.type, content: n.stringify(e.content, r, i), tag: "span", classes: ["token", e.type], attributes: {}, language: r, parent: i};
		s.type == "comment" && (s.attributes.spellcheck = "true");
		if (e.alias) {
			var o = t.util.type(e.alias) === "Array" ? e.alias : [e.alias];
			Array.prototype.push.apply(s.classes, o)
		}
		t.hooks.run("wrap", s);
		var u = "";
		for (var a in s.attributes)u += a + '="' + (s.attributes[a] || "") + '"';
		return "<" + s.tag + ' class="' + s.classes.join(" ") + '" ' + u + ">" + s.content + "</" + s.tag + ">"
	};
	if (!self.document) {
		if (!self.addEventListener)return self.Prism;
		self.addEventListener("message", function (e) {
			var n = JSON.parse(e.data), r = n.language, i = n.code;
			self.postMessage(JSON.stringify(t.util.encode(t.tokenize(i, t.languages[r]))));
			self.close()
		}, !1);
		return self.Prism
	}
	var r = document.getElementsByTagName("script");
	r = r[r.length - 1];
	if (r) {
		t.filename = r.src;
		document.addEventListener && !r.hasAttribute("data-manual") && document.addEventListener("DOMContentLoaded", t.highlightAll)
	}
	return self.Prism
}();
typeof module != "undefined" && module.exports && (module.exports = Prism);
;
Prism.languages.markup = {
	comment: /<!--[\w\W]*?-->/g,
	prolog: /<\?.+?\?>/,
	doctype: /<!DOCTYPE.+?>/,
	cdata: /<!\[CDATA\[[\w\W]*?]]>/i,
	tag: {pattern: /<\/?[\w:-]+\s*(?:\s+[\w:-]+(?:=(?:("|')(\\?[\w\W])*?\1|[^\s'">=]+))?\s*)*\/?>/gi, inside: {tag: {pattern: /^<\/?[\w:-]+/i, inside: {punctuation: /^<\/?/, namespace: /^[\w-]+?:/}}, "attr-value": {pattern: /=(?:('|")[\w\W]*?(\1)|[^\s>]+)/gi, inside: {punctuation: /=|>|"/g}}, punctuation: /\/?>/g, "attr-name": {pattern: /[\w:-]+/g, inside: {namespace: /^[\w-]+?:/}}}},
	entity: /\&#?[\da-z]{1,8};/gi
}, Prism.hooks.add("wrap", function (t) {
	"entity" === t.type && (t.attributes.title = t.content.replace(/&amp;/, "&"))
});
;
Prism.languages.css = {comment: /\/\*[\w\W]*?\*\//g, atrule: {pattern: /@[\w-]+?.*?(;|(?=\s*{))/gi, inside: {punctuation: /[;:]/g}}, url: /url\((["']?).*?\1\)/gi, selector: /[^\{\}\s][^\{\};]*(?=\s*\{)/g, property: /(\b|\B)[\w-]+(?=\s*:)/gi, string: /("|')(\\?.)*?\1/g, important: /\B!important\b/gi, punctuation: /[\{\};:]/g, "function": /[-a-z0-9]+(?=\()/gi}, Prism.languages.markup && (Prism.languages.insertBefore("markup", "tag", {
	style: {
		pattern: /<style[\w\W]*?>[\w\W]*?<\/style>/gi,
		inside: {tag: {pattern: /<style[\w\W]*?>|<\/style>/gi, inside: Prism.languages.markup.tag.inside}, rest: Prism.languages.css},
		alias: "language-css"
	}
}), Prism.languages.insertBefore("inside", "attr-value", {"style-attr": {pattern: /\s*style=("|').+?\1/gi, inside: {"attr-name": {pattern: /^\s*style/gi, inside: Prism.languages.markup.tag.inside}, punctuation: /^\s*=\s*['"]|['"]\s*$/, "attr-value": {pattern: /.+/gi, inside: Prism.languages.css}}, alias: "language-css"}}, Prism.languages.markup.tag));
;
Prism.languages.css.selector = {pattern: /[^\{\}\s][^\{\}]*(?=\s*\{)/g, inside: {"pseudo-element": /:(?:after|before|first-letter|first-line|selection)|::[-\w]+/g, "pseudo-class": /:[-\w]+(?:\(.*\))?/g, "class": /\.[-:\.\w]+/g, id: /#[-:\.\w]+/g}}, Prism.languages.insertBefore("css", "ignore", {hexcode: /#[\da-f]{3,6}/gi, entity: /\\[\da-f]{1,8}/gi, number: /[\d%\.]+/g});
;
Prism.languages.clike = {
	comment: [{pattern: /(^|[^\\])\/\*[\w\W]*?\*\//g, lookbehind: !0}, {pattern: /(^|[^\\:])\/\/.*?(\r?\n|$)/g, lookbehind: !0}],
	string: /("|')(\\?.)*?\1/g,
	"class-name": {pattern: /((?:(?:class|interface|extends|implements|trait|instanceof|new)\s+)|(?:catch\s+\())[a-z0-9_\.\\]+/gi, lookbehind: !0, inside: {punctuation: /(\.|\\)/}},
	keyword: /\b(if|else|while|do|for|return|in|instanceof|function|new|try|throw|catch|finally|null|break|continue)\b/g,
	"boolean": /\b(true|false)\b/g,
	"function": {pattern: /[a-z0-9_]+\(/gi, inside: {punctuation: /\(/}},
	number: /\b-?(0x[\dA-Fa-f]+|\d*\.?\d+([Ee]-?\d+)?)\b/g,
	operator: /[-+]{1,2}|!|<=?|>=?|={1,3}|&{1,2}|\|?\||\?|\*|\/|\~|\^|\%/g,
	ignore: /&(lt|gt|amp);/gi,
	punctuation: /[{}[\];(),.:]/g
};
;
Prism.languages.javascript = Prism.languages.extend("clike", {
	keyword: /\b(break|case|catch|class|const|continue|debugger|default|delete|do|else|enum|export|extends|false|finally|for|function|get|if|implements|import|in|instanceof|interface|let|new|null|package|private|protected|public|return|set|static|super|switch|this|throw|true|try|typeof|var|void|while|with|yield)\b/g,
	number: /\b-?(0x[\dA-Fa-f]+|\d*\.?\d+([Ee]-?\d+)?|NaN|-?Infinity)\b/g
}), Prism.languages.insertBefore("javascript", "keyword", {regex: {pattern: /(^|[^/])\/(?!\/)(\[.+?]|\\.|[^/\r\n])+\/[gim]{0,3}(?=\s*($|[\r\n,.;})]))/g, lookbehind: !0}}), Prism.languages.markup && Prism.languages.insertBefore("markup", "tag", {script: {pattern: /<script[\w\W]*?>[\w\W]*?<\/script>/gi, inside: {tag: {pattern: /<script[\w\W]*?>|<\/script>/gi, inside: Prism.languages.markup.tag.inside}, rest: Prism.languages.javascript}, alias: "language-javascript"}});
;
Prism.languages.php = Prism.languages.extend("clike", {
	keyword: /\b(and|or|xor|array|as|break|case|cfunction|class|const|continue|declare|default|die|do|else|elseif|enddeclare|endfor|endforeach|endif|endswitch|endwhile|extends|for|foreach|function|include|include_once|global|if|new|return|static|switch|use|require|require_once|var|while|abstract|interface|public|implements|private|protected|parent|throw|null|echo|print|trait|namespace|final|yield|goto|instanceof|finally|try|catch)\b/gi,
	constant: /\b[A-Z0-9_]{2,}\b/g,
	comment: {pattern: /(^|[^\\])(\/\*[\w\W]*?\*\/|(^|[^:])(\/\/|#).*?(\r?\n|$))/g, lookbehind: !0}
}), Prism.languages.insertBefore("php", "keyword", {delimiter: /(\?>|<\?php|<\?)/gi, variable: /(\$\w+)\b/gi, "package": {pattern: /(\\|namespace\s+|use\s+)[\w\\]+/g, lookbehind: !0, inside: {punctuation: /\\/}}}), Prism.languages.insertBefore("php", "operator", {property: {pattern: /(->)[\w]+/g, lookbehind: !0}}), Prism.languages.markup && (Prism.hooks.add("before-highlight", function (e) {
	"php" === e.language && (e.tokenStack = [], e.backupCode = e.code, e.code = e.code.replace(/(?:<\?php|<\?)[\w\W]*?(?:\?>)/gi, function (n) {
		return e.tokenStack.push(n), "{{{PHP" + e.tokenStack.length + "}}}"
	}))
}), Prism.hooks.add("before-insert", function (e) {
	"php" === e.language && (e.code = e.backupCode, delete e.backupCode)
}), Prism.hooks.add("after-highlight", function (e) {
	if ("php" === e.language) {
		for (var n, a = 0; n = e.tokenStack[a]; a++)e.highlightedCode = e.highlightedCode.replace("{{{PHP" + (a + 1) + "}}}", Prism.highlight(n, e.grammar, "php"));
		e.element.innerHTML = e.highlightedCode
	}
}), Prism.hooks.add("wrap", function (e) {
	"php" === e.language && "markup" === e.type && (e.content = e.content.replace(/(\{\{\{PHP[0-9]+\}\}\})/g, '<span class="token php">$1</span>'))
}), Prism.languages.insertBefore("php", "comment", {markup: {pattern: /<[^?]\/?(.*?)>/g, inside: Prism.languages.markup}, php: /\{\{\{PHP[0-9]+\}\}\}/g}));
;
Prism.languages.coffeescript = Prism.languages.extend("javascript", {
	comment: [/([#]{3}\s*\r?\n(.*\s*\r*\n*)\s*?\r?\n[#]{3})/g, /(\s|^)([#]{1}[^#^\r^\n]{2,}?(\r?\n|$))/g],
	keyword: /\b(this|window|delete|class|extends|namespace|extend|ar|let|if|else|while|do|for|each|of|return|in|instanceof|new|with|typeof|try|catch|finally|null|undefined|break|continue)\b/g
}), Prism.languages.insertBefore("coffeescript", "keyword", {"function": {pattern: /[a-z|A-z]+\s*[:|=]\s*(\([.|a-z\s|,|:|{|}|\"|\'|=]*\))?\s*-&gt;/gi, inside: {"function-name": /[_?a-z-|A-Z-]+(\s*[:|=])| @[_?$?a-z-|A-Z-]+(\s*)| /g, operator: /[-+]{1,2}|!|=?&lt;|=?&gt;|={1,2}|(&amp;){1,2}|\|?\||\?|\*|\//g}}, "attr-name": /[_?a-z-|A-Z-]+(\s*:)| @[_?$?a-z-|A-Z-]+(\s*)| /g});
;
Prism.languages.scss = Prism.languages.extend("css", {
	comment: {pattern: /(^|[^\\])(\/\*[\w\W]*?\*\/|\/\/.*?(\r?\n|$))/g, lookbehind: !0},
	atrule: /@[\w-]+(?=\s+(\(|\{|;))/gi,
	url: /([-a-z]+-)*url(?=\()/gi,
	selector: /([^@;\{\}\(\)]?([^@;\{\}\(\)]|&|\#\{\$[-_\w]+\})+)(?=\s*\{(\}|\s|[^\}]+(:|\{)[^\}]+))/gm
}), Prism.languages.insertBefore("scss", "atrule", {keyword: /@(if|else if|else|for|each|while|import|extend|debug|warn|mixin|include|function|return|content)|(?=@for\s+\$[-_\w]+\s)+from/i}), Prism.languages.insertBefore("scss", "property", {variable: /((\$[-_\w]+)|(#\{\$[-_\w]+\}))/i}), Prism.languages.insertBefore("scss", "ignore", {
	placeholder: /%[-_\w]+/i,
	statement: /\B!(default|optional)\b/gi,
	"boolean": /\b(true|false)\b/g,
	"null": /\b(null)\b/g,
	operator: /\s+([-+]{1,2}|={1,2}|!=|\|?\||\?|\*|\/|\%)\s+/g
});
;
Prism.languages.sql = {
	comment: {pattern: /(^|[^\\])(\/\*[\w\W]*?\*\/|((--)|(\/\/)|#).*?(\r?\n|$))/g, lookbehind: !0},
	string: {pattern: /(^|[^@])("|')(\\?[\s\S])*?\2/g, lookbehind: !0},
	variable: /@[\w.$]+|@("|'|`)(\\?[\s\S])+?\1/g,
	"function": /\b(?:COUNT|SUM|AVG|MIN|MAX|FIRST|LAST|UCASE|LCASE|MID|LEN|ROUND|NOW|FORMAT)(?=\s*\()/ig,
	keyword: /\b(?:ACTION|ADD|AFTER|ALGORITHM|ALTER|ANALYZE|APPLY|AS|ASC|AUTHORIZATION|BACKUP|BDB|BEGIN|BERKELEYDB|BIGINT|BINARY|BIT|BLOB|BOOL|BOOLEAN|BREAK|BROWSE|BTREE|BULK|BY|CALL|CASCADE|CASCADED|CASE|CHAIN|CHAR VARYING|CHARACTER VARYING|CHECK|CHECKPOINT|CLOSE|CLUSTERED|COALESCE|COLUMN|COLUMNS|COMMENT|COMMIT|COMMITTED|COMPUTE|CONNECT|CONSISTENT|CONSTRAINT|CONTAINS|CONTAINSTABLE|CONTINUE|CONVERT|CREATE|CROSS|CURRENT|CURRENT_DATE|CURRENT_TIME|CURRENT_TIMESTAMP|CURRENT_USER|CURSOR|DATA|DATABASE|DATABASES|DATETIME|DBCC|DEALLOCATE|DEC|DECIMAL|DECLARE|DEFAULT|DEFINER|DELAYED|DELETE|DENY|DESC|DESCRIBE|DETERMINISTIC|DISABLE|DISCARD|DISK|DISTINCT|DISTINCTROW|DISTRIBUTED|DO|DOUBLE|DOUBLE PRECISION|DROP|DUMMY|DUMP|DUMPFILE|DUPLICATE KEY|ELSE|ENABLE|ENCLOSED BY|END|ENGINE|ENUM|ERRLVL|ERRORS|ESCAPE|ESCAPED BY|EXCEPT|EXEC|EXECUTE|EXIT|EXPLAIN|EXTENDED|FETCH|FIELDS|FILE|FILLFACTOR|FIRST|FIXED|FLOAT|FOLLOWING|FOR|FOR EACH ROW|FORCE|FOREIGN|FREETEXT|FREETEXTTABLE|FROM|FULL|FUNCTION|GEOMETRY|GEOMETRYCOLLECTION|GLOBAL|GOTO|GRANT|GROUP|HANDLER|HASH|HAVING|HOLDLOCK|IDENTITY|IDENTITY_INSERT|IDENTITYCOL|IF|IGNORE|IMPORT|INDEX|INFILE|INNER|INNODB|INOUT|INSERT|INT|INTEGER|INTERSECT|INTO|INVOKER|ISOLATION LEVEL|JOIN|KEY|KEYS|KILL|LANGUAGE SQL|LAST|LEFT|LIMIT|LINENO|LINES|LINESTRING|LOAD|LOCAL|LOCK|LONGBLOB|LONGTEXT|MATCH|MATCHED|MEDIUMBLOB|MEDIUMINT|MEDIUMTEXT|MERGE|MIDDLEINT|MODIFIES SQL DATA|MODIFY|MULTILINESTRING|MULTIPOINT|MULTIPOLYGON|NATIONAL|NATIONAL CHAR VARYING|NATIONAL CHARACTER|NATIONAL CHARACTER VARYING|NATIONAL VARCHAR|NATURAL|NCHAR|NCHAR VARCHAR|NEXT|NO|NO SQL|NOCHECK|NOCYCLE|NONCLUSTERED|NULLIF|NUMERIC|OF|OFF|OFFSETS|ON|OPEN|OPENDATASOURCE|OPENQUERY|OPENROWSET|OPTIMIZE|OPTION|OPTIONALLY|ORDER|OUT|OUTER|OUTFILE|OVER|PARTIAL|PARTITION|PERCENT|PIVOT|PLAN|POINT|POLYGON|PRECEDING|PRECISION|PREV|PRIMARY|PRINT|PRIVILEGES|PROC|PROCEDURE|PUBLIC|PURGE|QUICK|RAISERROR|READ|READS SQL DATA|READTEXT|REAL|RECONFIGURE|REFERENCES|RELEASE|RENAME|REPEATABLE|REPLICATION|REQUIRE|RESTORE|RESTRICT|RETURN|RETURNS|REVOKE|RIGHT|ROLLBACK|ROUTINE|ROWCOUNT|ROWGUIDCOL|ROWS?|RTREE|RULE|SAVE|SAVEPOINT|SCHEMA|SELECT|SERIAL|SERIALIZABLE|SESSION|SESSION_USER|SET|SETUSER|SHARE MODE|SHOW|SHUTDOWN|SIMPLE|SMALLINT|SNAPSHOT|SOME|SONAME|START|STARTING BY|STATISTICS|STATUS|STRIPED|SYSTEM_USER|TABLE|TABLES|TABLESPACE|TEMP(?:ORARY)?|TEMPTABLE|TERMINATED BY|TEXT|TEXTSIZE|THEN|TIMESTAMP|TINYBLOB|TINYINT|TINYTEXT|TO|TOP|TRAN|TRANSACTION|TRANSACTIONS|TRIGGER|TRUNCATE|TSEQUAL|TYPE|TYPES|UNBOUNDED|UNCOMMITTED|UNDEFINED|UNION|UNPIVOT|UPDATE|UPDATETEXT|USAGE|USE|USER|USING|VALUE|VALUES|VARBINARY|VARCHAR|VARCHARACTER|VARYING|VIEW|WAITFOR|WARNINGS|WHEN|WHERE|WHILE|WITH|WITH ROLLUP|WITHIN|WORK|WRITE|WRITETEXT)\b/gi,
	"boolean": /\b(?:TRUE|FALSE|NULL)\b/gi,
	number: /\b-?(0x)?\d*\.?[\da-f]+\b/g,
	operator: /\b(?:ALL|AND|ANY|BETWEEN|EXISTS|IN|LIKE|NOT|OR|IS|UNIQUE|CHARACTER SET|COLLATE|DIV|OFFSET|REGEXP|RLIKE|SOUNDS LIKE|XOR)\b|[-+]{1}|!|[=<>]{1,2}|(&){1,2}|\|?\||\?|\*|\//gi,
	punctuation: /[;[\]()`,.]/g
};
;
