Vue.component('feed-back', {
  template: `
    <div id='my-feedback'>
        <div class='my-title2 title'>跟进记录</div>
        <div class='my-ta-content'>
            <textarea class='my-textarea' placeholder="简要填写跟进记录" maxlength="100" v-model="textareaText"></textarea>
            <div class='my-num' v-cloak>{{textareaText.length}}/100</div>
        </div>
        <div class='my-title upload-title'>
            <div class='title1 title'>图片<span class="font-gray">(选填、提交问题的截图)</span></div>
            <div class='title2' v-cloak>{{feedbackimg.length}}/4</div>
        </div>
        <div class='my-scroll'>
            <div class='my-scroll-content'>
                <div class='my-image-view' v-for="(item, index) in feedbackimg">
                    <img class='image' :src="item.file.src"/>
                    <img class='close' :src="getPath('image/close.png')" v-tap="{methods:onCloseImage,index:index}"/>
                </div>
                <img :class="['image', feedbackimg.length>2? 'hasTop': '']" :src="getPath('image/upload.png')" v-tap="{methods:chooseType}"/>
            </div>
        </div>
        <div class='my-btn-success button' v-tap="{methods: commitLog, is_pass: 2}">{{ isAddComment? '保存': '确认'}}</div>
        <div v-if="reportedInfo.status_type == 1&&!isAddComment" class='my-btn-fail button' v-tap="{methods: commitLog, is_pass: -1}">不通过</div>
        <input @change="fileChange($event)" type="file" id="upload_file" multiple accept="image/*" style="display: none;"/>
    </div>
  `,
  props: {
    staticPath: {
      type: String,
      default: './static'
    }
  },
  data(){
    return {
      reported_id: '',
      isAddComment: false,
      textareaText: "",  //文本框内容
      feedbackText: "",  //联系方式内容
      feedbackWidth:'35', //图片组宽度
      feedbackimg:[],   //反馈上传的图片路径
      limit:4, //限制图片上传的数量
      uploadFiles: [],
        reportedInfo:{}
    }
  },
  mounted() {

    this.$nextTick(function () {
        this.isAddComment = Boolean(getQueryString('isAddComment'));
        this.reported_id = getQueryString('reported_id');
        this.setFeedbackWidth();
        this.initStyle();
    })
      setTimeout(()=>{
          this.reportedInfo =  this.$parent.reportedInfo
      },500)
  },
  methods:{
      //设置图片组宽度
      setFeedbackWidth(){
          this.feedbackWidth=(this.feedbackimg.length+1)*35;
      },
      chooseType() {
          document.getElementById('upload_file').click();
      },
      fileChange(e) {
          if (!e.target.files[0].size) return;
          this.fileList(e.target);
          e.target.value = ''
      },
      fileList(fileList) {
          let files = fileList.files;
          for (let i = 0; i < files.length; i++) {
              //判断是否为文件夹
              if (files[i].type != '') {
                  this.fileAdd(files[i]);
              } else {
                  //文件夹处理
                  this.folders(fileList.items[i]);
              }
          }
      },
      //文件夹处理
      folders(files) {
          let _this = this;
          //判断是否为原生file
          if (files.kind) {
              files = files.webkitGetAsEntry();
          }
          files.createReader().readEntries(function (file) {
              for (let i = 0; i < file.length; i++) {
                  if (file[i].isFile) {
                      _this.foldersAdd(file[i]);
                  } else {
                      _this.folders(file[i]);
                  }
              }
          });
      },
      foldersAdd(entry) {
          let _this = this;
          entry.file(function (file) {
              _this.fileAdd(file)
          })
      },
      fileAdd(file) {
          if (this.limit !== undefined) this.limit--;
          if (this.limit !== undefined && this.limit < 0){
              mui.toast('最多只能上传4张图片');
              return false;
          }
          //总大小
          this.size = this.size + file.size;
          //判断是否为图片文件
          if (file.type.indexOf('image') == -1) {
              mui.toast('请选择图片文件');
          } else {
              let reader = new FileReader();
              let image = new Image();
              let _this = this;
              reader.readAsDataURL(file);
              reader.onload = function () {
                  file.src = this.result;
                  image.onload = function(){
                      let width = image.width;
                      let height = image.height;
                      file.width = width;
                      file.height = height;
                      _this.$set(_this.feedbackimg, _this.feedbackimg.length, {file});
                      _this.setFeedbackWidth();
                  };
                  image.src= file.src;
              }
          }
      },
      onCloseImage(event) {
          var _this=this;
          mui.confirm('删除图片？', '提示', ['确认', '取消'], function(e) {
              if (e.index == 0) {
                  _this.feedbackimg.splice(event.index, 1);
                  if (_this.limit !== undefined) _this.limit = 4-_this.feedbackimg.length;
              }
          });
      },
      commitLog(options) {
        if (this.textareaText == ""){
            this.$toast('意见内容不能为空！');
            return false;
        }
        const reportedInfo = this.$parent.reportedInfo;
        console.log('$parent',this.$parent)

        const formData = new FormData();
        formData.append('reported_id', reportedInfo.id);
        formData.append('status_type', this.isAddComment? '0': reportedInfo.status_type);
        formData.append('is_pass', options.is_pass);
        formData.append('content', this.textareaText);
        formData.append('changeCommission', this.$parent.changeCommission);
        formData.append('build_fold', this.$parent.build_fold);
        formData.append('uid', '84')
        this.feedbackimg.forEach(img => {
            formData.append('imgs[]', img.file);
        });
          formData.forEach((value, key) => {
              console.log("key %s: value %s", key, value);
          })
        $.ajax({
            url: DOMAINNAME+'agentapi/agentAjax/examineReported',
            type: 'POST',
            data: formData,
            cache: false,
            contentType: false,    //不可缺
            processData: false,    //不可缺
            complete: res => {
                // console.log()
              if (res.status == 200) {
                const result = JSON.parse(res.responseText);
                  if (result.success) {
                      this.$toast('上传成功');
                      setLocation('pages/customer/record_detail.html?id='+reported_id);
                  } else {
                      this.$toast(result.message);
                  }
              } else {
                  this.$toast('上传错误');
              }
            }
        });
      },
      initStyle() {
        var css = document.createElement('style');
        css.type='text/css';
        css.setAttributeNode(document.createAttribute('scopped'));
        css.appendChild(document.createTextNode(`@import "${this.staticPath}/css/components/feed-back.css";`));
        this.$el.appendChild(css);
      },
      getPath(path) {
        return `${this.staticPath}/${path}`;
      }
  }
})
