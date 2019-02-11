<?php
//Highest error reporting level
error_reporting(-1);
//Print errors directly as output plgSearchJResearch_Search
ini_set("display_errors", true);
?>

<style>
	.cajaRespuestas{
		border: 1px solid #00629b;
	}
	.titulo {
		margin:0px; 
		height: 5rem; 
		color: white; 
		background-color: #00629b; 
		padding: 13px
	}
	.chatBot{
		height: 300px;
		overflow: auto;
	}
	.chatBotHistory{
		margin:10px; 
		/*max-height:300px; */
		overflow-y: auto; 
		overflow-x: hidden;
	}
	.chatBotChatOut{
		border-radius: 15px; 
		margin: 10px 0; 
		line-height: 1.3; 
		min-width: 76%; 
		min-height: 30px; 
		background-color: #00629b;
		color: white;
		padding: 7px 13px;
		max-width: 85%; 
		display: inline-block;
		text-align: left;
	}
	.chatBotChatEntry{
		border-radius: 15px; 
		line-height: 1.3; 
		margin: 10px 0; 
		float: right; 
		min-height: 30px;
		background-color: #00a9e0;
		color: white;padding: 7px 13px; 
		display: inline-block;
		min-width: 25%;
		max-width: 85%;
		text-align: right;
	}
	.input{
		display:flex; 
		justify-content: space-between; 
		margin:7px; 
		border-top: 1px solid #00629b;
		border-bottom: 1px solid #00629b;
	}
	.humanQuery{
		margin-left: 10px; 
		border: none !important; 
		box-shadow: none !important; padding: 0 10px !important;
		width: 85% !important;
		margin: 0 !important;
	}
	.buttonInput{
		padding:4px; 
		border: none; 
		background-color: transparent;
		width: 15%;
		margin: 0 !important;
	}
	.cajaSuperior{
		display: flex;
		justify-content: space-between;
		background-color: #00629b;
	}
	.eyebrowCanvas{
		height: 50px !important;
		width: 50px !important;
		left: 20rem !important;
	}
	.blinkCanvas{
		height: 50px !important;
		width: 50px !important;
		left: 20rem !important;
	}
	.mainCanvas{
		left: 20rem !important;
		border-radius: 25px;
	}
</style>

<!-- HTML presented in the chat box-->
<div id="CajaRespuestas" class="cajaRespuestas">
	<div class="cajaSuperior">
		<h4 id="titulo" class="titulo">Chat</h4>
		<div>
			<a href="javascript:playsyncronized()">
				<div id="imagecontainer">
					<img id="exampleimg" class="iconoSpeaker" src="https://i.imgur.com/ork8hoP.gif" rel:animated_src="https://i.imgur.com/ork8hoP.gif" rel:auto_play="0" />
					<!--<img id="exampleimg" class="iconoSpeaker" src="https://media.giphy.com/media/GXgSnscEQBqTK/giphy.gif" rel:animated_src="https://media.giphy.com/media/GXgSnscEQBqTK/giphy.gif" rel:auto_play="0" />-->
					<!--<img id="exampleimg" class="iconoSpeaker" src="http://www.gifmania.com/Gif-Animados-Personas/Imagenes-Hombres/Hombres-Avergonzados/Albert-Einstein-avergonzado-49588.gif" rel:animated_src="http://www.gifmania.com/Gif-Animados-Personas/Imagenes-Hombres/Hombres-Avergonzados/Albert-Einstein-avergonzado-49588.gif" rel:auto_play="0" />-->
				</div>
			</a>
		</div>
	</div>
	<div id="chatBot" class="chatBot">
	    <div id="chatBotHistory" class="chatBotHistory">
	    	<section class="chatBotChatOut">¿En qué puedo ayudarte?</section>
	    </div>
	    <div id="chatBotThinkingIndicator">
	    	<img src="https://eyezen.es/wp-content/plugins/cupones-devialia/img/loader.gif" width="250" height="100" alt="" style="width: 100%">
	    </div>
	</div>
	<div id="input" class="input">
		<input id="humanInput" class="humanQuery" type="text" placeholder="Escriba su pregunta" />
	    <button id="send" class="buttonInput" type="button" onclick="responder();">
	    	<span class="glyphicon glyphicon-send"></span>
	    </button>
	</div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script>
	//Variable que contiene la query para ponerla en el html
	var query;
	//Variable que contiene la respuesta para ponerla en el html
	var answer;

	// Ocultamos al principio el indicador de cargando
	jQuery( document ).ready(function() {
		jQuery('#chatBotThinkingIndicator').hide();	
	});

	//Para detectar el intro en el input
	jQuery("#humanInput").on('keyup', function (e) {
	    if (e.keyCode == 13) {
	        responder();
	    }
	});

	//Tratamos el input introducido por el usuario
	function responder() {
		if(jQuery("#humanInput").val() == ''){
			alert("No ha introducido nada. Si quiere hablar con el bot escriba algo.");
		}else{
			pensando(true);
			query = jQuery("#humanInput").val();
			jQuery.ajax({
				type: 'POST',
				//Hacemos uso de com_ajax para manejar llamadas ajax hacia modulos/plugins
				url: 'index.php?option=com_ajax&ignoreMessages&module=botdialogflow&method=setQuery&format=json',
    			data: {query: query},
			    success: function(response){
			        //console.log('Ha ido bien:'+ response.data);
			        answer = replaceURLWithHTMLLinks(response.data);
			    },
			    error: function(error){
			    	//console.log(error);
			    },
			    //La hacemos asincrona para que no avance hasta que no se escriba el resultado en la variable
			    async: false 
			});
			mostrarRespuesta();
			pensando(false);
		}
	};

	//Convertimos si hay algún link en link pulsable.
    function replaceURLWithHTMLLinks(text){
      var exp = /(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
      return text.replace(exp,"<a href='$1' style='color:white'>$1</a>"); 
    }

    //Muestra un mensaje de cargando al usuario durante las llamadas.
	function pensando(on){
		//console.log(on);
		//Si estamos consultando una query, desactivamos el input y mostramos actividad
		if(on){
			jQuery("#humanInput").attr('disabled', 'disabled');
			jQuery('#chatBotThinkingIndicator').show();
			jQuery('#chatBot').animate({ scrollTop: jQuery('#chatBot')[0].scrollHeight }, 0);
		}else{
			//Sino limpiamos el input y quitamos el indicador de actividad
			jQuery("#humanInput").removeAttr('disabled');
			jQuery("#humanInput").val('');
			jQuery("#humanInput").focus();
			jQuery('#chatBotThinkingIndicator').hide();
		}
	};

	//Mostramos los resultados en la caja.
	function mostrarRespuesta(){
		//Mostramos la query
		query = jQuery("#humanInput").val();
        	var entryDiv = jQuery('<section class="chatBotChatEntry"></section>');
       		entryDiv.html(query);
		jQuery('#chatBotHistory').append(entryDiv);
		//Mostramos la respuesta del bot
		var outDiv = jQuery('<section class="chatBotChatOut"></section>');
       		outDiv.html(answer);
		jQuery('#chatBotHistory').append(outDiv);
		jQuery('#chatBot').animate({ scrollTop: jQuery('#chatBot')[0].scrollHeight }, 'slow');
		playsyncronized();
	};

	// ---------------------
	// Playing TTS in sync
	// ---------------------


	(function (root, factory) {
	    if (typeof define === 'function' && define.amd) {
	        define([], factory);
	    } else if (typeof exports === 'object') {
	        module.exports = factory();
	    } else {
	        root.SuperGif = factory();
	    }
	}(this, function () {
	    // Generic functions
	    var bitsToNum = function (ba) {
	        return ba.reduce(function (s, n) {
	            return s * 2 + n;
	        }, 0);
	    };

	    var byteToBitArr = function (bite) {
	        var a = [];
	        for (var i = 7; i >= 0; i--) {
	            a.push( !! (bite & (1 << i)));
	        }
	        return a;
	    };

	    // Stream
	    /**
	     * @constructor
	     */
	    // Make compiler happy.
	    var Stream = function (data) {
	        this.data = data;
	        this.len = this.data.length;
	        this.pos = 0;

	        this.readByte = function () {
	            if (this.pos >= this.data.length) {
	                throw new Error('Attempted to read past end of stream.');
	            }
	            if (data instanceof Uint8Array)
	                return data[this.pos++];
	            else
	                return data.charCodeAt(this.pos++) & 0xFF;
	        };

	        this.readBytes = function (n) {
	            var bytes = [];
	            for (var i = 0; i < n; i++) {
	                bytes.push(this.readByte());
	            }
	            return bytes;
	        };

	        this.read = function (n) {
	            var s = '';
	            for (var i = 0; i < n; i++) {
	                s += String.fromCharCode(this.readByte());
	            }
	            return s;
	        };

	        this.readUnsigned = function () { // Little-endian.
	            var a = this.readBytes(2);
	            return (a[1] << 8) + a[0];
	        };
	    };

	    var lzwDecode = function (minCodeSize, data) {
	        // TODO: Now that the GIF parser is a bit different, maybe this should get an array of bytes instead of a String?
	        var pos = 0; // Maybe this streaming thing should be merged with the Stream?
	        var readCode = function (size) {
	            var code = 0;
	            for (var i = 0; i < size; i++) {
	                if (data.charCodeAt(pos >> 3) & (1 << (pos & 7))) {
	                    code |= 1 << i;
	                }
	                pos++;
	            }
	            return code;
	        };

	        var output = [];

	        var clearCode = 1 << minCodeSize;
	        var eoiCode = clearCode + 1;

	        var codeSize = minCodeSize + 1;

	        var dict = [];

	        var clear = function () {
	            dict = [];
	            codeSize = minCodeSize + 1;
	            for (var i = 0; i < clearCode; i++) {
	                dict[i] = [i];
	            }
	            dict[clearCode] = [];
	            dict[eoiCode] = null;

	        };

	        var code;
	        var last;

	        while (true) {
	            last = code;
	            code = readCode(codeSize);

	            if (code === clearCode) {
	                clear();
	                continue;
	            }
	            if (code === eoiCode) break;

	            if (code < dict.length) {
	                if (last !== clearCode) {
	                    dict.push(dict[last].concat(dict[code][0]));
	                }
	            }
	            else {
	                if (code !== dict.length) throw new Error('Invalid LZW code.');
	                dict.push(dict[last].concat(dict[last][0]));
	            }
	            output.push.apply(output, dict[code]);

	            if (dict.length === (1 << codeSize) && codeSize < 12) {
	                // If we're at the last code and codeSize is 12, the next code will be a clearCode, and it'll be 12 bits long.
	                codeSize++;
	            }
	        }

	        // I don't know if this is technically an error, but some GIFs do it.
	        //if (Math.ceil(pos / 8) !== data.length) throw new Error('Extraneous LZW bytes.');
	        return output;
	    };


	    // The actual parsing; returns an object with properties.
	    var parseGIF = function (st, handler) {
	        handler || (handler = {});

	        // LZW (GIF-specific)
	        var parseCT = function (entries) { // Each entry is 3 bytes, for RGB.
	            var ct = [];
	            for (var i = 0; i < entries; i++) {
	                ct.push(st.readBytes(3));
	            }
	            return ct;
	        };

	        var readSubBlocks = function () {
	            var size, data;
	            data = '';
	            do {
	                size = st.readByte();
	                data += st.read(size);
	            } while (size !== 0);
	            return data;
	        };

	        var parseHeader = function () {
	            var hdr = {};
	            hdr.sig = st.read(3);
	            hdr.ver = st.read(3);
	            if (hdr.sig !== 'GIF') throw new Error('Not a GIF file.'); // XXX: This should probably be handled more nicely.
	            hdr.width = st.readUnsigned();
	            hdr.height = st.readUnsigned();

	            var bits = byteToBitArr(st.readByte());
	            hdr.gctFlag = bits.shift();
	            hdr.colorRes = bitsToNum(bits.splice(0, 3));
	            hdr.sorted = bits.shift();
	            hdr.gctSize = bitsToNum(bits.splice(0, 3));

	            hdr.bgColor = st.readByte();
	            hdr.pixelAspectRatio = st.readByte(); // if not 0, aspectRatio = (pixelAspectRatio + 15) / 64
	            if (hdr.gctFlag) {
	                hdr.gct = parseCT(1 << (hdr.gctSize + 1));
	            }
	            handler.hdr && handler.hdr(hdr);
	        };

	        var parseExt = function (block) {
	            var parseGCExt = function (block) {
	                var blockSize = st.readByte(); // Always 4
	                var bits = byteToBitArr(st.readByte());
	                block.reserved = bits.splice(0, 3); // Reserved; should be 000.
	                block.disposalMethod = bitsToNum(bits.splice(0, 3));
	                block.userInput = bits.shift();
	                block.transparencyGiven = bits.shift();

	                block.delayTime = st.readUnsigned();

	                block.transparencyIndex = st.readByte();

	                block.terminator = st.readByte();

	                handler.gce && handler.gce(block);
	            };

	            var parseComExt = function (block) {
	                block.comment = readSubBlocks();
	                handler.com && handler.com(block);
	            };

	            var parsePTExt = function (block) {
	                // No one *ever* uses this. If you use it, deal with parsing it yourself.
	                var blockSize = st.readByte(); // Always 12
	                block.ptHeader = st.readBytes(12);
	                block.ptData = readSubBlocks();
	                handler.pte && handler.pte(block);
	            };

	            var parseAppExt = function (block) {
	                var parseNetscapeExt = function (block) {
	                    var blockSize = st.readByte(); // Always 3
	                    block.unknown = st.readByte(); // ??? Always 1? What is this?
	                    block.iterations = st.readUnsigned();
	                    block.terminator = st.readByte();
	                    handler.app && handler.app.NETSCAPE && handler.app.NETSCAPE(block);
	                };
	                var parseTalkrExt = function (block) {
	                    var blockSize = st.readByte(); 
	                    block.frameIndex = st.readUnsigned(); 
	                    block.numframes = st.readUnsigned(); 
	                    block.channelIdentifier = st.read(1);
	                    block.reserved1 = st.readByte();
	                    block.reserved2 = st.readByte();
	                    block.reserved3 = st.readByte();
	                    block.terminator = st.readByte();
	                    handler.app && handler.app.TALKR && handler.app.TALKR(block);
	                };                

	                var parseUnknownAppExt = function (block) {
	                    block.appData = readSubBlocks();
	                    // FIXME: This won't work if a handler wants to match on any identifier.
	                    handler.app && handler.app[block.identifier] && handler.app[block.identifier](block);
	                };

	                var blockSize = st.readByte(); // Always 11
	                block.identifier = st.read(8);
	                block.authCode = st.read(3);
	                switch (block.identifier) {
	                    case 'NETSCAPE':
	                        parseNetscapeExt(block);
	                        break;
	                    case 'TALKRAPP':
	                        parseTalkrExt(block);
	                        break;                        
	                    default:
	                        parseUnknownAppExt(block);
	                        break;
	                }
	            };

	            var parseUnknownExt = function (block) {
	                block.data = readSubBlocks();
	                handler.unknown && handler.unknown(block);
	            };

	            block.label = st.readByte();
	            switch (block.label) {
	                case 0xF9:
	                    block.extType = 'gce';
	                    parseGCExt(block);
	                    break;
	                case 0xFE:
	                    block.extType = 'com';
	                    parseComExt(block);
	                    break;
	                case 0x01:
	                    block.extType = 'pte';
	                    parsePTExt(block);
	                    break;
	                case 0xFF:
	                    block.extType = 'app';
	                    parseAppExt(block);
	                    break;
	                default:
	                    block.extType = 'unknown';
	                    parseUnknownExt(block);
	                    break;
	            }
	        };

	        var parseImg = function (img) {
	            var deinterlace = function (pixels, width) {
	                // Of course this defeats the purpose of interlacing. And it's *probably*
	                // the least efficient way it's ever been implemented. But nevertheless...
	                var newPixels = new Array(pixels.length);
	                var rows = pixels.length / width;
	                var cpRow = function (toRow, fromRow) {
	                    var fromPixels = pixels.slice(fromRow * width, (fromRow + 1) * width);
	                    newPixels.splice.apply(newPixels, [toRow * width, width].concat(fromPixels));
	                };

	                // See appendix E.
	                var offsets = [0, 4, 2, 1];
	                var steps = [8, 8, 4, 2];

	                var fromRow = 0;
	                for (var pass = 0; pass < 4; pass++) {
	                    for (var toRow = offsets[pass]; toRow < rows; toRow += steps[pass]) {
	                        cpRow(toRow, fromRow)
	                        fromRow++;
	                    }
	                }

	                return newPixels;
	            };

	            img.leftPos = st.readUnsigned();
	            img.topPos = st.readUnsigned();
	            img.width = st.readUnsigned();
	            img.height = st.readUnsigned();

	            var bits = byteToBitArr(st.readByte());
	            img.lctFlag = bits.shift();
	            img.interlaced = bits.shift();
	            img.sorted = bits.shift();
	            img.reserved = bits.splice(0, 2);
	            img.lctSize = bitsToNum(bits.splice(0, 3));

	            if (img.lctFlag) {
	                img.lct = parseCT(1 << (img.lctSize + 1));
	            }

	            img.lzwMinCodeSize = st.readByte();

	            var lzwData = readSubBlocks();

	            img.pixels = lzwDecode(img.lzwMinCodeSize, lzwData);

	            if (img.interlaced) { // Move
	                img.pixels = deinterlace(img.pixels, img.width);
	            }

	            handler.img && handler.img(img);
	        };

	        var parseBlock = function () {
	            var block = {};
	            block.sentinel = st.readByte();

	            switch (String.fromCharCode(block.sentinel)) { // For ease of matching
	                case '!':
	                    block.type = 'ext';
	                    parseExt(block);
	                    break;
	                case ',':
	                    block.type = 'img';
	                    parseImg(block);
	                    break;
	                case ';':
	                    block.type = 'eof';
	                    handler.eof && handler.eof(block);
	                    break;
	                default:
	                    throw new Error('Unknown block: 0x' + block.sentinel.toString(16)); // TODO: Pad this with a 0.
	            }

	            if (block.type !== 'eof') setTimeout(parseBlock, 0);
	        };

	        var parse = function () {
	            parseHeader();
	            setTimeout(parseBlock, 0);
	        };

	        parse();
	    };

	   

	    // tick_to_frame is a function taking a frame index as arg.  Passing -1 clears the canvas
	    var gestureController = function(start_frame, num_frames, tick_to_frame){
	        var self = this;
	        self.frame_indexes = [];
	        for(var i = 0; i < num_frames; ++i){
	            self.frame_indexes.push(start_frame + i)
	        }

	        var anim_copy = null;
	        var paused = false;
	        self.on_anim_complete = null;


	        self.restore_default_anim = function(){
	            self.anim = anim_copy.slice();
	            if(self.loop && self.anim.length > 0){
	                self.anim[self.anim.length-1].delay = self.loop_end_delay + Math.random() * self.loop_end_delay_random;
	            }                  
	        }        
	        // default_delay - the amount of time each frame is displayed during the ping-pong anim
	        // loop_end_delay - null for no looping.  Otherwise, the minimum amount of time before looping
	        // loop_end_delay_random - defaults to 0.  A random amount of ms added to loop_end_delay so each loop is different
	        self.construct_default_anim = function(default_delay, loop_end_delay, loop_end_delay_random){

	            if(!default_delay) default_delay = 100;
	            self.default_delay =  default_delay;

	            self.loop = loop_end_delay != null;

	            if(!loop_end_delay) loop_end_delay = 0;
	            if(!loop_end_delay_random) loop_end_delay_random = 0;

	            self.loop_end_delay = loop_end_delay;
	            self.loop_end_delay_random = loop_end_delay_random;

	            self.anim = [];
	            self.frame_indexes.forEach(function(i){
	                self.anim.push([i, default_delay]);
	            });;
	            var frame_indexes_reversed = self.frame_indexes.slice().reverse();
	            
	            // Don't duplicate last frame
	            frame_indexes_reversed.shift()

	            // tick back to the first frame
	            frame_indexes_reversed.forEach(function(i){
	                self.anim.push([i, default_delay]);
	            });
	            
	            var calculated_loop_delay = default_delay;  
	            if( self.loop ){
	                calculated_loop_delay =  loop_end_delay + Math.random() * loop_end_delay_random;
	                self.on_anim_complete = function(){
	                    
	                    tick_anim();
	                }
	            } else {
	                self.on_anim_complete = null;
	            }
	            self.anim.push([-1, calculated_loop_delay]);
	            anim_copy = self.anim.slice();
	            
	        }
	        // Doesn't effect the current anim.
	        self.update_default_anim = function(custom_anim, loop_end_delay, loop_end_delay_random){
	            self.loop = loop_end_delay != null;
	            self.loop_end_delay = loop_end_delay;
	            self.loop_end_delay_random = loop_end_delay_random;            
	            anim_copy = custom_anim.slice();
	            if(self.loop){
	                self.on_anim_complete = function(){
	                    tick_anim();
	                } 
	            } else {
	                self.on_anim_complete = null;
	            }
	        }

	        var tick_anim = function(){
	            var anim = self.anim;
	            if( paused ){
	                paused = false;
	                return;
	            }
	            if(anim){
	                if( anim.length > 0 && anim[0] ){
	                    var i = anim[0][0];
	                    var d = anim[0][1];
	                    anim.shift();
	                    if(d && i ){
	                        tick_to_frame(i)
	                        if(d > 0){
	                            setTimeout(tick_anim, d);
	                        } else {
	                            tick_anim();
	                        }
	                    }
	                } else {
	                    self.restore_default_anim();
	                    if(self.on_anim_complete){
	                        self.on_anim_complete();
	                    }
	                }
	            }
	        }
	        // construct a default animation
	        self.construct_default_anim(self.default_delay);


	        self.clear = function(){
	            tick_to_frame(-1);
	            self.anim = [];
	            self.on_anim_complete = null;
	        }
	        self.pause = function(){
	            paused = true;
	        }
	        self.resume = function(){
	            paused = false;
	            tick_anim();
	        }        
	        self.play = function(customOnFinished){
	            if(customOnFinished){
	                self.on_anim_complete = customOnFinished;
	            }
	            paused = false; 
	            tick_anim();
	        }

	        return self;
	    }


	    var SuperGif = function ( opts ) {
	        var options = {
	            //viewport position
	            vp_l: 0,
	            vp_t: 0,
	            vp_w: null,
	            vp_h: null,
	            //canvas sizes
	            c_w: null,
	            c_h: null
	        };
	        for (var i in opts ) { options[i] = opts[i] }
	        if (options.vp_w && options.vp_h) options.is_vp = true;

	        var stream;
	        var hdr;

	        var loadError = null;
	        var loading = false;

	        var transparency = null;
	        var delay = null;
	        var disposalMethod = null;
	        var disposalRestoreFromIdx = null;
	        var lastDisposalMethod = null;
	        var frame = null;
	        var lastImg = null;
	        var playing = true;
	        var forward = true;
	        var defaultFrameTime = 10;

	        var ctx_scaled = false;

	        var frames = [];
	        var frameOffsets = []; // elements have .x and .y properties

	        var gif = options.gif;
	        if (typeof options.auto_play == 'undefined')
	            options.auto_play = (!gif.getAttribute('rel:auto_play') || gif.getAttribute('rel:auto_play') == '1');

	        // the keys to this dict are the channel identifiers.  '0' for blink, '1' for eyebrows.
	        var talkr_channels = {};

	        if (typeof options.autoplay_blinks == 'undefined')
	            options.autoplay_blinks = (!gif.getAttribute('rel:autoplay_blinks') || gif.getAttribute('rel:autoplay_blinks') == '1');
	        
	        var autoplay_blinks = options.autoplay_blinks;

	        // Variables for play_for_duration
	        var totalLipsyncAnimTime = 0;
	        var playDuration = 0;
	        var startTime = 0;
	        var pingpong = false;

	        // We start to looping back at this frame,
	        //leaving any animation data after it available for 
	        // eyebrow-raises, blinks, or other
	        var last_lipsync_frame = 0;

	        var overrideFrameTime = null;

	        var onEndListener = (options.hasOwnProperty('on_end') ? options.on_end : null);
	        var loopDelay = (options.hasOwnProperty('loop_delay') ? options.loop_delay : 0);
	        var overrideLoopMode = (options.hasOwnProperty('loop_mode') ? options.loop_mode : 'auto');
	        var drawWhileLoading = (options.hasOwnProperty('draw_while_loading') ? options.draw_while_loading : true);
	        var showProgressBar = drawWhileLoading ? (options.hasOwnProperty('show_progress_bar') ? options.show_progress_bar : true) : false;
	        var progressBarHeight = (options.hasOwnProperty('progressbar_height') ? options.progressbar_height : 25);
	        var progressBarBackgroundColor = (options.hasOwnProperty('progressbar_background_color') ? options.progressbar_background_color : 'rgba(255,255,255,0.4)');
	        var progressBarForegroundColor = (options.hasOwnProperty('progressbar_foreground_color') ? options.progressbar_foreground_color : 'rgba(255,0,22,.8)');

	        var clear = function () {
	            transparency = null;
	            delay = null;
	            lastDisposalMethod = disposalMethod;
	            disposalMethod = null;
	            frame = null;
	        };

	        // XXX: There's probably a better way to handle catching exceptions when
	        // callbacks are involved.
	        var doParse = function () {
	            try {
	                parseGIF(stream, handler);
	            }
	            catch (err) {
	                doLoadError('parse');
	            }
	        };

	        var doText = function (text) {
	            toolbar.innerHTML = text; // innerText? Escaping? Whatever.
	            toolbar.style.visibility = 'visible';
	        };

	        var setSizes = function(w, h) {
	            canvas.width = w * get_canvas_scale();
	            canvas.height = h * get_canvas_scale();
	            canvas.style.zIndex  = 1;

	            toolbar.style.minWidth = ( w * get_canvas_scale() ) + 'px';

	            tmpCanvas.width = w;
	            tmpCanvas.height = h;
	            tmpCanvas.style.width = w + 'px';
	            tmpCanvas.style.height = h + 'px';
	            tmpCanvas.getContext('2d').setTransform(1, 0, 0, 1, 0, 0);

	            eyebrowCanvas.width = w;
	            eyebrowCanvas.height = h;
	            eyebrowCanvas.style.zIndex = 3;
	            eyebrowCanvas.getContext('2d').setTransform(1, 0, 0, 1, 0, 0);      

	            blinkCanvas.width = w;
	            blinkCanvas.height = h;
	            blinkCanvas.style.zIndex = 2;
	            blinkCanvas.getContext('2d').setTransform(1, 0, 0, 1, 0, 0);  


	            // make all canvas elements absolute relative to the parent div.
	            // We want them to overlap and act like the single img div that the
	            // parent div is going to replace            
	            canvas.style.position = "absolute" 
	            eyebrowCanvas.style.position = "absolute"
	            blinkCanvas.style.position = "absolute"

	            // Do our best to apply css that will make the canvas elements behave
	            // like the replaced img div.  Works with and without <center>
	            canvas.style.left = 0;
	            eyebrowCanvas.style.left = 0;
	            blinkCanvas.style.left = 0;
	            canvas.style.right = 0;
	            eyebrowCanvas.style.right = 0;
	            blinkCanvas.style.right = 0;  

	            canvas.style.margin = "0 auto";
	            eyebrowCanvas.style.margin = "0 auto";
	            blinkCanvas.style.margin = "0 auto";

	            // Add a classname to the canvases for the cases where the css above
	            // doesn't work (like when you are trying to get make the gif have 100% width
	            // TODO: find a better way to make our collection of div and canvas elements
	            // work like the img they are replacing.
	            canvas.className += "mainCanvas superGifCanvas"
	            eyebrowCanvas.className += "eyebrowCanvas superGifCanvas"
	            blinkCanvas.className += "blinkCanvas superGifCanvas"

	        };

	        var setFrameOffset = function(frame, offset) {
	            if (!frameOffsets[frame]) {
	                frameOffsets[frame] = offset;
	                return;
	            }
	            if (typeof offset.x !== 'undefined') {
	                frameOffsets[frame].x = offset.x;
	            }
	            if (typeof offset.y !== 'undefined') {
	                frameOffsets[frame].y = offset.y;
	            }
	        };

	        var doShowProgress = function (pos, length, draw) {
	            if (draw && showProgressBar) {
	                var height = progressBarHeight;
	                var left, mid, top, width;
	                if (options.is_vp) {
	                    if (!ctx_scaled) {
	                        top = (options.vp_t + options.vp_h - height);
	                        height = height;
	                        left = options.vp_l;
	                        mid = left + (pos / length) * options.vp_w;
	                        width = canvas.width;
	                    } else {
	                        top = (options.vp_t + options.vp_h - height) / get_canvas_scale();
	                        height = height / get_canvas_scale();
	                        left = (options.vp_l / get_canvas_scale() );
	                        mid = left + (pos / length) * (options.vp_w / get_canvas_scale());
	                        width = canvas.width / get_canvas_scale();
	                    }
	                    //some debugging, draw rect around viewport
	                    if (false) {
	                        if (!ctx_scaled) {
	                            var l = options.vp_l, t = options.vp_t;
	                            var w = options.vp_w, h = options.vp_h;
	                        } else {
	                            var l = options.vp_l/get_canvas_scale(), t = options.vp_t/get_canvas_scale();
	                            var w = options.vp_w/get_canvas_scale(), h = options.vp_h/get_canvas_scale();
	                        }
	                        ctx.rect(l,t,w,h);
	                        ctx.stroke();
	                    }
	                }
	                else {
	                    top = (canvas.height - height) / (ctx_scaled ? get_canvas_scale() : 1);
	                    mid = ((pos / length) * canvas.width) / (ctx_scaled ? get_canvas_scale() : 1);
	                    width = canvas.width / (ctx_scaled ? get_canvas_scale() : 1 );
	                    height /= ctx_scaled ? get_canvas_scale() : 1;
	                }

	                ctx.fillStyle = progressBarBackgroundColor;
	                ctx.fillRect(mid, top, width - mid, height);

	                ctx.fillStyle = progressBarForegroundColor;
	                ctx.fillRect(0, top, mid, height);
	            }
	        };

	        var doLoadError = function (originOfError) {
	            var drawError = function () {
	                ctx.fillStyle = 'black';
	                ctx.fillRect(0, 0, options.c_w ? options.c_w : hdr.width, options.c_h ? options.c_h : hdr.height);
	                ctx.strokeStyle = 'red';
	                ctx.lineWidth = 3;
	                ctx.moveTo(0, 0);
	                ctx.lineTo(options.c_w ? options.c_w : hdr.width, options.c_h ? options.c_h : hdr.height);
	                ctx.moveTo(0, options.c_h ? options.c_h : hdr.height);
	                ctx.lineTo(options.c_w ? options.c_w : hdr.width, 0);
	                ctx.stroke();
	            };

	            loadError = originOfError;
	            hdr = {
	                width: gif.width,
	                height: gif.height
	            }; // Fake header.
	            frames = [];
	            drawError();
	        };

	        var doHdr = function (_hdr) {
	            hdr = _hdr;
	            setSizes(hdr.width, hdr.height)
	        };

	        var doGCE = function (gce) {
	            pushFrame();
	            clear();
	            transparency = gce.transparencyGiven ? gce.transparencyIndex : null;
	            delay = gce.delayTime;
	            disposalMethod = gce.disposalMethod;
	            // We don't have much to do with the rest of GCE.
	        };

	        var pushFrame = function () {
	            if (!frame) return;
	            // originally, topPos, leftPos were set to 0, and width, wight set from hdr.width, hdr.height. 
	            // So every frame in frames was the size of the whole image.
	            
	            var topPos = 0;
	            var leftPos = 0;
	            var width = hdr.width;
	            var height = hdr.height;      

	            // todo: is lastImg the best way to get the dimensions of the frame? 
	            if(lastImg){
	                // We can save 75%-85% memory of a typical talkr file by only storing non-transparent 
	                // portions of each frame.  This assumes that the gif frame has optimal settings for topPos, 
	                // leftPos, width & height.  Given the memory savings, and the fact that there was existing 
	                // support for frameOffsets, I'm not sure why frameOffsets were always set to 0 in
	                // https://github.com/buzzfeed/libgif-js
	                topPos = lastImg.topPos;
	                leftPos = lastImg.leftPos;
	                width = lastImg.width;
	                height = lastImg.height;
	            }

	            frames.push({
	                            data: frame.getImageData(leftPos, topPos, width, height),
	                            delay: delay
	                        });
	            frameOffsets.push({ x: leftPos, y: topPos });

	            var delayTime = delay == 0 ? defaultFrameTime : delay;
	            // Count the total animation time for all lip-sync frames
	            // so we can estimate when to reverse ticking direction
	            if(frames.length <= last_lipsync_frame){
	                totalLipsyncAnimTime = totalLipsyncAnimTime + delayTime * 10;
	            }            
	        };

	        var doImg = function (img) {
	            if (!frame) frame = tmpCanvas.getContext('2d');

	            var currIdx = frames.length;

	            //ct = color table, gct = global color table
	            var ct = img.lctFlag ? img.lct : hdr.gct; // TODO: What if neither exists?

	            /*
	            Disposal method indicates the way in which the graphic is to
	            be treated after being displayed.
	            Values :    0 - No disposal specified. The decoder is
	                            not required to take any action.
	                        1 - Do not dispose. The graphic is to be left
	                            in place.
	                        2 - Restore to background color. The area used by the
	                            graphic must be restored to the background color.
	                        3 - Restore to previous. The decoder is required to
	                            restore the area overwritten by the graphic with
	                            what was there prior to rendering the graphic.
	                            Importantly, "previous" means the frame state
	                            after the last disposal of method 0, 1, or 2.
	            */
	            if (currIdx > 0) {
	                if (lastDisposalMethod === 3) {

	                    var bPreserveTalkrTransparency = false;
	                    if( currIdx >= last_lipsync_frame){
	                        // we are on a talkr extension frame.  We need to keep the transparency.
	                        bPreserveTalkrTransparency = true;
	                    }
	                    // Restore to previous
	                    // If we disposed every frame including first frame up to this point, then we have
	                    // no composited frame to restore to. In this case, restore to background instead.
	                    if (disposalRestoreFromIdx !== null && !bPreserveTalkrTransparency) {
	                        frame.putImageData(frames[disposalRestoreFromIdx].data, 0, 0);
	                    } else {
	                        frame.clearRect(lastImg.leftPos, lastImg.topPos, lastImg.width, lastImg.height);
	                    }
	                } else {
	                    disposalRestoreFromIdx = currIdx - 1;
	                }

	                if (lastDisposalMethod === 2) {
	                    // Restore to background color
	                    // Browser implementations historically restore to transparent; we do the same.
	                    // http://www.wizards-toolkit.org/discourse-server/viewtopic.php?f=1&t=21172#p86079
	                    frame.clearRect(lastImg.leftPos, lastImg.topPos, lastImg.width, lastImg.height);
	                }
	            }
	            // else, Undefined/Do not dispose.
	            // frame contains final pixel data from the last frame; do nothing

	            //Get existing pixels for img region after applying disposal method
	            var imgData = frame.getImageData(img.leftPos, img.topPos, img.width, img.height);

	            //apply color table colors
	            img.pixels.forEach(function (pixel, i) {
	                // imgData.data === [R,G,B,A,R,G,B,A,...]
	                if (pixel !== transparency) {
	                    imgData.data[i * 4 + 0] = ct[pixel][0];
	                    imgData.data[i * 4 + 1] = ct[pixel][1];
	                    imgData.data[i * 4 + 2] = ct[pixel][2];
	                    imgData.data[i * 4 + 3] = 255; // Opaque.
	                }
	            });

	            frame.putImageData(imgData, img.leftPos, img.topPos);

	            if (!ctx_scaled) {
	                ctx.scale(get_canvas_scale(),get_canvas_scale());
	                ctx_scaled = true;
	            }

	            // We could use the on-page canvas directly, except that we draw a progress
	            // bar for each image chunk (not just the final image).
	            if (drawWhileLoading) {
	                ctx.drawImage(tmpCanvas, 0, 0);
	                drawWhileLoading = options.auto_play;
	            }

	            lastImg = img;
	        };

	        var player = (function () {
	            var i = -1;
	            var iterationCount = 0;

	            var showingInfo = false;
	            var pinned = false;

	            /**
	             * Gets the index of the frame "up next".
	             * @returns {number}
	             */
	            var getNextFrameNo = function () {
	                var delta = (forward ? 1 : -1);
	                return (i + delta + frames.length) % frames.length;
	            };

	            var stepFrame = function (amount) { // XXX: Name is confusing.
	                i = i + amount;

	                putFrame();
	            };

	            var step = (function () {
	                var stepping = false;

	                var completeLoop = function () {
	                    if (onEndListener !== null)
	                        onEndListener(gif);
	                    iterationCount++;

	                    if (overrideLoopMode !== false || iterationCount < 0) {
	                        doStep();
	                    } else {
	                        stepping = false;
	                        playing = false;
	                    }
	                };

	                var doStep = function () {
	                    stepping = playing;
	                    if (!stepping) return;

	                    stepFrame(forward ? 1 : -1); // Forward is only false with pingpong                    
	                    var delay = frames[i].delay * 10;
	                    if (!delay) delay = defaultFrameTime * 10; // FIXME: Should this even default at all? What should it be?

	                    if (overrideFrameTime) {
	                        delay = overrideFrameTime;
	                    }

	                    var nextFrameNo = getNextFrameNo();
	                    if (nextFrameNo === 0) {
	                        delay += loopDelay;
	                        setTimeout(completeLoop, delay);
	                    } else {
	                        setTimeout(doStep, delay);
	                    }
	                };

	                return function () {
	                    if (!stepping) setTimeout(doStep, 0);
	                };
	            }());


	            var setFrameIndexAndDirection = function() {
	                // if ping-ponging, these are equivalent
	                i = Math.abs(i);

	                var num_lip_frames = Math.min(frames.length, last_lipsync_frame )

	                var timeElapsed = Date.now() - startTime;
	                var timeLeft = playDuration - timeElapsed;

	                // We assume/enforce a constant per-frame time here.
	                var perFrameTime = totalLipsyncAnimTime / num_lip_frames;
	                if (overrideFrameTime) {
	                    perFrameTime = overrideFrameTime;
	                }
	                if (forward) {
	                    if (perFrameTime * (i + 1) > timeLeft) {
	                        forward = false;
	                    }
	                }

	                if (i >= num_lip_frames) {
	                    i = num_lip_frames > 2 ? num_lip_frames - 2 : num_lip_frames - 1;
	                    forward = false;
	                } else if (i == 0) {
	                    forward = true;
	                    // If there is only time to rapidly open/close the mouth, 
	                    // better to keep it closed.
	                    if (timeLeft < perFrameTime * 2) {
	                        pause();
	                    }
	                }
	            }

	            var putFrame = function() {
	                var offset;
	                i = parseInt(i, 10);

	                if (pingpong) {
	                    setFrameIndexAndDirection();
	                }

	                if (i > frames.length - 1){
	                    i = 0;
	                }

	                if (i < 0){
	                    i = 0;
	                }

	                offset = frameOffsets[i];
	                
	                if(frames[i].data.width != tmpCanvas.width || frames[i].data.height != tmpCanvas.height ){
	                    // To save memory, only non-transparent pixels are stored in the frames array.
	                    // On talkr files, this can reduce memory requirements by 80%.  Because
	                    // each frame only writes it's data on the canvas, stale data can potencially 
	                    // stick around unless we clear it.  Copying frame 0 to the canvas is how we are clearing
	                    tmpCanvas.getContext("2d").putImageData(frames[0].data, 0, 0);
	                }

	                tmpCanvas.getContext("2d").putImageData(frames[i].data, offset.x, offset.y);
	                ctx.globalCompositeOperation = "copy";
	                ctx.drawImage(tmpCanvas, 0, 0);

	            };

	            var play = function () {
	                pingpong = false;
	                playing = true;
	                step();
	            };
	            // Randomly trigger an eyebrow raise at some point during speech.
	            var trigger_eyebrow_anim = function(duration){
	                // if eyebrow channel exists
	                if ('1' in talkr_channels){
	                    if( Math.random() > 0.65 ) {
	                        var eyebrows = talkr_channels['1'];
	                        if( eyebrows && eyebrows.controller){
	                            setTimeout( eyebrows.controller.play, Math.random() * duration);
	                        }
	                    }
	                }

	            }            
	            var play_for_duration = function(duration, overrideFrameDuration, bPlayEyebrowAnim) {
	                if (overrideFrameDuration) {
	                    //console.log("overriding frame duration: " + overrideFrameDuration)
	                    overrideFrameTime = overrideFrameDuration
	                } else {
	                    overrideFrameTime = null;
	                }
	                if (duration > 0 && duration < 300) {
	                    overrideFrameTime = 125;
	                    // snapping the mouth open and close looks bad.
	                    duration = 300;
	                }
	                if( bPlayEyebrowAnim ){
	                    trigger_eyebrow_anim(duration)
	                }
	                pingpong = true;
	                playing = true;
	                playDuration = duration;
	                startTime = Date.now();
	                step();
	            }

	            var pause = function() {
	                playing = false;
	            };


	            return {
	                init: function () {
	                    if (loadError) return;

	                    if ( ! (options.c_w && options.c_h) ) {
	                        ctx.scale(get_canvas_scale(),get_canvas_scale());
	                    }

	                    if (options.auto_play) {
	                        step();
	                    }
	                    else {
	                        i = 0;
	                        putFrame();
	                    }
	                },
	                step: step,
	                play: play,
	                pause: pause,
	                play_for_duration: function(duration, overrideFrameDuration, bPlayEyebrowAnim) {
	                    play_for_duration(duration, overrideFrameDuration, bPlayEyebrowAnim);
	                },                 
	                playing: playing,
	                move_relative: stepFrame,
	                current_frame: function() { return i; },
	                length: function() { return frames.length },
	                move_to: function ( frame_idx ) {
	                    i = frame_idx;
	                    putFrame();
	                }
	            }
	        }());

	        var doDecodeProgress = function (draw) {
	            doShowProgress(stream.pos, stream.data.length, draw);
	        };

	        var doNothing = function () {};
	        /**
	         * @param{boolean=} draw Whether to draw progress bar or not; this is not idempotent because of translucency.
	         *                       Note that this means that the text will be unsynchronized with the progress bar on non-frames;
	         *                       but those are typically so small (GCE etc.) that it doesn't really matter. TODO: Do this properly.
	         */
	        var withProgress = function (fn, draw) {
	            return function (block) {
	                fn(block);
	                doDecodeProgress(draw);
	            };
	        };


	        var handler = {
	            hdr: withProgress(doHdr),
	            gce: withProgress(doGCE),
	            com: withProgress(doNothing),
	            // I guess that's all for now.
	            app: {
	                // TODO: Is there much point in actually supporting iterations?
	                NETSCAPE: withProgress(doNothing),
	                TALKR: withProgress(function(block){

	                    talkr_channels[block.channelIdentifier] = {index:block.frameIndex, numframes:block.numframes};

	                    if( block.frameIndex > 0 ){
	                        if(last_lipsync_frame==0 || last_lipsync_frame >= block.frameIndex ){
	                            last_lipsync_frame = block.frameIndex;
	                        }
	                    }


	                })
	            },
	            img: withProgress(doImg, true),
	            eof: function (block) {
	                //toolbar.style.display = '';
	                pushFrame();
	                doDecodeProgress(false);
	                if ( ! (options.c_w && options.c_h) ) {
	                    canvas.width = hdr.width * get_canvas_scale();
	                    canvas.height = hdr.height * get_canvas_scale();
	                }
	                player.init();
	                loading = false;
	                if( last_lipsync_frame == 0 ){
	                    last_lipsync_frame = frames.length;
	                }
	                initialize_talkr_channels()
	                // Is there a better place for this?
	                canvas.parentNode.style.width = canvas.width + 'px';
	                canvas.parentNode.style.height =  canvas.height + 'px';

	                if (load_callback) {
	                    load_callback(gif);
	                }

	            }
	        };
	        var initialize_talkr_channels = function(){

	            var blink_key = '0';
	            var eyebrow_key = '1'
	            if(talkr_channels[blink_key]){
	                var numframes = talkr_channels[blink_key].numframes;
	                var startframe = talkr_channels[blink_key].index;
	                if( startframe + numframes < frames.length ){
	                    talkr_channels[blink_key].controller  = new gestureController(startframe, numframes, function(index){
	                        blinkCanvas.getContext("2d").clearRect(0,0,eyebrowCanvas.width, eyebrowCanvas.height);
	                        if( index && index >= 0 && index < frameOffsets.length && index < frames.length ){
	                            var offset = frameOffsets[index];
	                            blinkCanvas.getContext("2d").putImageData(frames[index].data, offset.x, offset.y);
	                        }
	                    });
	                }
	                if( autoplay_blinks ){
	                    // Create a default looping blink animation with 50ms frame time, looping every 3-8 seconds
	                    talkr_channels[blink_key].controller.construct_default_anim(50, 3000, 8000); 

	                    talkr_channels[blink_key].controller.play()
	                } 
	            }

	            if(talkr_channels[eyebrow_key]){
	                var numframes = talkr_channels[eyebrow_key].numframes;
	                var startframe = talkr_channels[eyebrow_key].index;                
	                talkr_channels[eyebrow_key].controller = new gestureController(startframe, numframes, function(index){
	                    eyebrowCanvas.getContext("2d").clearRect(0,0,eyebrowCanvas.width, eyebrowCanvas.height);
	                    if( index && index >= 0 && index < frameOffsets.length && index < frames.length ){
	                        var offset = frameOffsets[index];
	                        eyebrowCanvas.getContext("2d").putImageData(frames[index].data, offset.x, offset.y);
	                    }
	                });
	                if( startframe + numframes < frames.length ){
	                    // create a default eyebrow anim (if we have at least 4-frames)
	                    // This will play randomly on each call to play_for_duration.
	                    if(numframes>=4){
	                        var custom_eyebrow_anim = [
	                            [startframe, 100],
	                            [startframe+1, 100],
	                            [startframe+2, 150],
	                            [startframe+3, 400],
	                            [startframe+2, 150],
	                            [startframe+1, 100],
	                            [startframe, 100],
	                            [-1, 0]
	                        ];
	                        talkr_channels[eyebrow_key].controller.anim = custom_eyebrow_anim
	                        talkr_channels[eyebrow_key].controller.update_default_anim(custom_eyebrow_anim, null, null);
	                    }
	                }
	            }         
	        }
	        var destroy = function() {
	            if(player){
	                player.pause();
	            }
	            for (var key in talkr_channels) {
	                if(talkr_channels[key] && talkr_channels[key].controller){
	                    talkr_channels[key].controller.pause();
	                }
	            }

	            // We won't delete all of the data in the supergif object, but frames is the big one.
	            frames = [];
	            // lastImg has pixel data too and is sizable.
	            lastImg = null;
	        }
	        var init = function () {
	            var parent = gif.parentNode;

	            var div = document.createElement('div');
	            canvas = document.createElement('canvas');
	            ctx = canvas.getContext('2d');
	            toolbar = document.createElement('div');

	            tmpCanvas = document.createElement('canvas');
	            
	            // a separate canvas is used for eyebrow & blinks
	            eyebrowCanvas = document.createElement('canvas');
	            blinkCanvas = document.createElement('canvas');

	            div.width = canvas.width = gif.width;
	            div.height = canvas.height = gif.height;
	            toolbar.style.minWidth = gif.width + 'px';

	            div.className = 'jsgif';
	            toolbar.className = 'jsgif_toolbar';
	            div.appendChild(eyebrowCanvas);
	            div.appendChild(blinkCanvas);
	            div.appendChild(canvas);
	            div.appendChild(toolbar);

	            parent.insertBefore(div, gif);
	            parent.removeChild(gif);

	            if (options.c_w && options.c_h) setSizes(options.c_w, options.c_h);
	            initialized=true;
	        };

	        var get_canvas_scale = function() {
	            var scale;
	            if (options.max_width && hdr && hdr.width > options.max_width) {
	                scale = options.max_width / hdr.width;
	            }
	            else {
	                scale = 1;
	            }
	            return scale;
	        }

	        var canvas, ctx, toolbar, tmpCanvas, eyebrowCanvas, blinkCanvas;
	        var initialized = false;
	        var load_callback = false;

	        var eyebrowGestureController, blinkGestureController;
	        var load_setup = function(callback) {
	            if (loading) return false;
	            if (callback) load_callback = callback;
	            else load_callback = false;

	            loading = true;
	            frames = [];
	            clear();
	            disposalRestoreFromIdx = null;
	            lastDisposalMethod = null;
	            frame = null;
	            lastImg = null;

	            return true;
	        }

	        return {
	            // play controls
	            play_for_duration: function(duration, overrideFrameDuration, bPlayEyebrowAnim) {
	                if(bPlayEyebrowAnim==null){
	                    bPlayEyebrowAnim = true;
	                }
	                player.play_for_duration(duration, overrideFrameDuration, bPlayEyebrowAnim);
	            },            
	            play: player.play,
	            pause: player.pause,
	            move_relative: player.move_relative,
	            move_to: player.move_to,

	            destroy      : function() { destroy() },
	            // getters for instance vars
	            get_playing      : function() { return playing },
	            get_canvas       : function() { return canvas },
	            get_canvas_scale : function() { return get_canvas_scale() },
	            get_loading      : function() { return loading },
	            get_auto_play    : function() { return options.auto_play },
	            get_length       : function() { return player.length() },
	            get_current_frame: function() { return player.current_frame() },
	            get_talkr_ext    : function(key) {
	                return talkr_channels[key];
	            },
	            load_url: function(src,callback){
	                if (!load_setup(callback)) return;

	                var h = new XMLHttpRequest();
	                // new browsers (XMLHttpRequest2-compliant)
	                h.open('GET', src, true);

	                if ('overrideMimeType' in h) {
	                    h.overrideMimeType('text/plain; charset=x-user-defined');
	                }

	                // old browsers (XMLHttpRequest-compliant)
	                else if ('responseType' in h) {
	                    h.responseType = 'arraybuffer';
	                }

	                // IE9 (Microsoft.XMLHTTP-compliant)
	                else {
	                    h.setRequestHeader('Accept-Charset', 'x-user-defined');
	                }

	                h.onloadstart = function() {
	                    // Wait until connection is opened to replace the gif element with a canvas to avoid a blank img
	                    if (!initialized) init();
	                };
	                h.onload = function(e) {
	                    if (this.status != 200) {
	                        doLoadError('xhr - response');
	                    }
	                    // emulating response field for IE9
	                    if (!('response' in this)) {
	                        this.response = new VBArray(this.responseText).toArray().map(String.fromCharCode).join('');
	                    }
	                    var data = this.response;
	                    if (data.toString().indexOf("ArrayBuffer") > 0) {
	                        data = new Uint8Array(data);
	                    }

	                    stream = new Stream(data);
	                    setTimeout(doParse, 0);
	                };
	                h.onprogress = function (e) {
	                    if (e.lengthComputable) doShowProgress(e.loaded, e.total, true);
	                };
	                h.onerror = function() { doLoadError('xhr'); };
	                h.send();
	            },
	            load: function (callback) {
	                this.load_url(gif.getAttribute('rel:animated_src') || gif.src,callback);
	            },
	            load_raw: function(arr, callback) {
	                if (!load_setup(callback)) return;
	                if (!initialized) init();
	                stream = new Stream(arr);
	                setTimeout(doParse, 0);
	            },
	            set_frame_offset: setFrameOffset
	        };
	    };

	    return SuperGif;
	}));

	// Load the Gif
	var sup1 = new SuperGif({ gif: document.getElementById('exampleimg'), max_width: 50} );
	sup1.load(function(){
	});
	// play the specified text 
	function playsyncronized(){
		if(!'speechSynthesis' in window){
			instructions.innerHTML = "Speech synthesis is not supported in this browser.  Sorry.";
			document.getElementById('ttsoptions').style.visibility = "hidden";
		}
		else {
			if(speechSynthesis.speaking){
				return;
			}
			if(answer != undefined){
				var text = answer;
			}else{
				var text = "¿En que puedo ayudarte?";
			}
			// get the selected voice
			var voice = speechSynthesis.getVoices().filter(function(voice){
					return voice.name == "spanish";
				})[0];

		    // Splitting each utterance up using punctuation is important.  Intra-utterance
		    // punctuation will add silence to the tts which looks bad unless the mouth stops moving
		    // correctly. Better to split it into separate utterances so play_for_duration will move when
		    // talking, and be on frame 0 when not. 

		    // split everything betwen deliminators [.?,!], but include the deliminator.
		    var substrings = text.match(/[^.?,!]+[.?,!]?/g);
		    for (var i = 0, l = substrings.length; i < l; ++i) {
		        var str = substrings[i].trim();

		        // Make sure there is something to say other than the deliminator
		        var numpunc = (str.match(/[.?,!]/g) || []).length;
		        if (str.length - numpunc > 0) {

		        	// suprisingly decent approximation for multiple languages.

		       		// if you change the rate, you would have to adjust
		            var speakingDurationEstimate = str.length * 50;
		            // Chinese needs a different calculation.  Haven't tried other Asian languages.
		            if (str.match(/[\u3400-\u9FBF]/)) {
		                speakingDurationEstimate = str.length * 200;
		            }

		            var msg = new SpeechSynthesisUtterance();

	            	(function(dur){
	                	msg.addEventListener('start', function(){
	                		sup1.play_for_duration(dur);
	                })})(speakingDurationEstimate);

		            // The end event is too inacurate to use for animation,
		            // but perhaps it could be used elsewhere.  You might need to push 
		            // the msg to an array or aggressive garbage collection fill prevent the callback
		            // from firing.
		            //msg.addEventListener('end', function (){console.log("too late")}			                
		            
		            msg.text = str;
		            //change voice here
		            msg.voice = voice;

		            window.speechSynthesis.speak(msg);
		        }
		    }
		}
	}
</script>