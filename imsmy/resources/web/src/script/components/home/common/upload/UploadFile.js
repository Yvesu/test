import React, { Component } from 'react'
// import { Upload, Icon, Modal } from 'antd'
import { Progress,Icon,message,Upload,Button } from 'antd'
import TimeFormat from 'utils/format'
import Fetch from 'utils/fetch'

//upload
class UploadFile extends Component {
  constructor(props){
    super(props)
    this.state={
      token:'',
      nameTimes:'',
      percent:0,
      UploadedSize:'0mb',
      timeConsuming:'00秒',
      upStart:true,
      upLoading:false,
      upEnding:false,
      isError:false,
    }

  }

   handleChangeUpload=()=>{
    var that = this
    var uploader =  window.Qiniu.uploader({
         runtimes: 'html5,flash,html4',    //上传模式,依次退化
         browse_button: 'pickfiles',       //上传选择的点选按钮，**必需**
         // uptoken_url: ,            //Ajax请求upToken的Url，**强烈建议设置**（服务端提供）
         uptoken : `${that.state.token}`, //若未指定uptoken_url,则必须指定 uptoken ,uptoken由其他程序生成
         // unique_names: true, // 默认 false，key为文件名。若开启该选项，SDK为自动生成上传成功后的key（文件名）。
         // save_key: true,   // 默认 false。若在服务端生成uptoken的上传策略中指定了 `sava_key`，则开启，SDK会忽略对key的处理
         domain: 'http://upload.qiniu.com',   //bucket 域名，下载资源时用到，**必需**
         get_new_uptoken: false,  //设置上传文件的时候是否每次都重新获取新的token
         container: 'container',           //上传区域DOM ID，默认是browser_button的父元素，
         max_file_size: '10gb',           //最大文件体积限制
         flash_swf_url: 'js/plupload/Moxie.swf',  //引入flash,相对路径
         max_retries: 1,                   //上传失败最大重试次数
         dragdrop: false,                   //开启可拖曳上传
         // drop_element: 'container',        //拖曳上传区域元素的ID，拖曳文件或文件夹后可触发上传
         chunk_size: '4mb',                //分块上传时，每片的体积
         auto_start: true,                 //选择文件后自动上传，若关闭需要自己绑定事件触发上传
         // multi_selection:false,
         filters : {
            // max_file_size : '100mb',
            // prevent_duplicates: true,
            // Specify what files to browse for
            mime_types: [
                // {title : "flv files", extensions : "flv"}, // 限定flv后缀上传格式上传
                {title : "Video files", extensions : "flv,mpg,mpeg,avi,wmv,mov,asf,rm,rmvb,mkv,m4v,mp4"}, // 限定flv,mpg,mpeg,avi,wmv,mov,asf,rm,rmvb,mkv,m4v,mp4后缀格式上传
                // {title : "Image files", extensions : "jpg,gif,png"}, // 限定jpg,gif,png后缀上传
                {title : "Zip files", extensions : "zip"} // 限定zip后缀上传
            ]
        },
         init: {
             'FilesAdded': function(up, files) {
                 window.plupload.each(files, function(file) {
                     // 文件添加进队列后,处理相关的事情
                     console.log(up,'添加到队列');
                     console.log(file,'添加的文件');
                     uploader.refresh()
                     uploader.start()
                 });
             },
             'BeforeUpload': function(up, file) {
                 // 每个文件上传前,处理相关的事情

             },
             'UploadProgress': function(up, file) {
                 // 每个文件上传时,处理相关的事情
                 // console.log(uploadFilePath);
                 console.log(file.percent + "%");
                 //将自动上传选项改为可选
                 if(that.props.changeAutoSubmit){
                   that.props.changeAutoSubmit(false)
                 }
                 //改变显示的区域
                 that.setState({
                   upStart:false,
                   upLoading:true
                 })
                 //上传中所花费的时间
                 var startTime = Date.parse(file._start_at)
                 var endTime = Date.parse(new Date())
                 var consumingTime = TimeFormat.uploadTime(endTime - startTime)
                 that.setState({
                   timeConsuming:consumingTime
                 })
                 //设置上传文件的大小
                 var UploadedSize = (file.loaded /1024 /1024).toFixed(2)
                 if(UploadedSize>1000){
                   var UploadedSizeGb = (UploadedSize /1024).toFixed(2) +'gb'
                   that.setState({
                     fileSize:UploadedSizeGb
                   })
                 }else{
                   that.setState({
                     UploadedSize:UploadedSize+'mb'
                   })
                 }
                 //设置百分比进度
                 if(file.percent>100 || file.percent === 99){
                   that.setState({
                     percent:99
                   })
                 }else{
                   that.setState({
                     percent:file.percent
                   })
                 }
                 //改变背景颜色
                 $('.upload_progress_color').css({ width: file.percent + '%'})
                 //取消上传
                 $('.cancel_uploading').click(function(event) {
                   /* Act on the event */
                     uploader.removeFile(file)
                     that.setState({
                       percent:0,
                       upStart:true,
                       upLoading:false
                       // status:'active'
                     })
                     //将自动上传选择重新禁用
                     if(that.props.changeAutoSubmit){
                       that.props.changeAutoSubmit(true)
                     }
                 });

             },
             'FileUploaded': function(up, file, info) {
                 // 每个文件上传成功后,处理相关的事情
                 // 其中 info.response 是文件上传成功后，服务端返回的json，形式如
                 // {
                 //    "hash": "Fh8xVqod2MQ1mocfI4S4KpRL6D98",
                 //    "key": "gogopher.jpg"
                 //  }
                 // 参考http://developer.qiniu.com/docs/v6/api/overview/up/response/simple-response.html
                 // console.log(file,'1');
                 // console.log(info,'2');
                 //获取上传文件的大小
                 if(that.props.getFileSize){
                   that.props.getFileSize(file.size)
                 }
                 if(info.status===200){
                   var newResponse = JSON.parse(info.response)
                   var newKey = newResponse.key
                   console.log(newKey,'key');
                   that.setState({
                     upLoading:false,
                     upEnding:true
                   })
                   //获取上传文件的key
                   if(that.props.getFileKey){
                     that.props.getFileKey(newKey)
                   }
                 }

                 //上传成功之后取消
                 $('.upload_file_re').click(function(event) {
                   /* Act on the event */
                     uploader.removeFile(file)
                     that.setState({
                       percent:0,
                       upStart:true,
                       upLoading:false,
                       upEnding:false,
                       UploadedSize:'0mb',
                       timeConsuming:'00秒',
                     })
                     //将文件的key清空
                     if(that.props.getFileKey){
                       that.props.getFileKey('')
                     }
                     //将自动上传选择重新禁用
                     if(that.props.changeAutoSubmit){
                       that.props.changeAutoSubmit(true)
                     }
                     //获取文件的大小清空
                     if(that.props.getFileSize){
                       that.props.getFileSize('')
                     }
                 });


             },
             'Error': function(up, err, errTip,info) {
                 //上传出错时,处理相关的事情
                 if(err.code && err.code===-601){
                   message.error('格式不支持');
                 }else{
                   message.error(errTip+'请重新上传')
                 }
                 //上传的时候出现错误 改变上传区域的样式
                 that.setState({
                   isError:true
                 })
                 $('.cancel_uploading').css({background: '#ff000'})
                 //重新上传
                 $('.cancel_uploading').click(function(event) {
                   /* Act on the event */
                     uploader.removeFile(file)
                     that.setState({
                       percent:0,
                       upStart:true,
                       upLoading:false
                       // status:'active'
                     })
                     //将自动上传选择重新禁用
                     if(that.props.changeAutoSubmit){
                       that.props.changeAutoSubmit(true)
                     }
                 });
                 //
                 // console.log(errTip,'ererTip');
             },
             'UploadComplete': function() {
                 //队列文件处理完毕后,处理相关的事情
                 // console.log('处理完毕');
             },
             'Key': function(up, file) {
                 // 若想在前端对每个文件的key进行个性化处理，可以配置该函数
                 // 该配置必须要在 unique_names: false , save_key: false 时才生效
                 const suffix ="." + file.name.replace(/^.+\./,'')
                 that.setState({
                   nameTimes:TimeFormat.NowTime()
                 })
                 const key = that.props.name+that.state.nameTimes+suffix
                 // console.log(file,'key');
                 return key
             }
         }
     })
   }

  render(){

    return(
      <div className='upload_file_box'>
        <div id="container" className="upload_file_container" style={{display:this.state.upStart===true? "block" : 'none'}}>
            <a className="choose_big_file" id="pickfiles" href="#">
              <p className='upload_file_symbol'>
                {/* <img src="./img/upload_icon.png" /> */}
                <img src="http://img.cdn.hivideo.com/web/img/upload_icon.png" />
              </p>
              <p>单个视频最大10G</p>
              <p>上传视频，即表示你已同意
                <a href="#">HiVideo上传服务条款</a>
              </p>
            </a>
        </div>
        <div className='upload_file_ing_box' style={{display:this.state.upLoading===true? "block" : 'none'}}>
          <div className='upload_progress'>
            <p className='cancel_uploading'>{this.state.isError===true? '重新上传' : "取消上传"}</p>
            <p>{`${this.state.percent}%`}</p>
            <p>已上传：<span style={{marginRight:15}}>{this.state.UploadedSize}</span>
                耗时：<span>{this.state.timeConsuming}</span></p>
          </div>
          <div className='upload_progress_color'></div>
        </div>
        <div className='upload_file_ending' style={{display:this.state.upEnding===true? "block" : 'none'}}>
          <p className="upload_file_re">取消发布</p>
          <p><Icon type="check-circle" />{'发布成功'}</p>
          <p>文件大小：<span style={{marginRight:15}}>{this.state.UploadedSize}</span>
            总共耗时：<span>{this.state.timeConsuming}</span></p>
        </div>
      </div>
    )
  }
componentDidMount(){
  let formData = new FormData()
  formData.append('type',"evideo")
  formData.append('location',"华东")
  Fetch.post({
    uri:'/api/test/cloudStorage/token',
    callback:(res)=>{
      // console.log(res);
      this.setState({
        token:res.token
      })
      this.handleChangeUpload()
    },
    formData:formData
  })
}

componentWillReceiveProps(nextProps){
  // console.log(nextProps,'hah');
  if(this.props.name !== nextProps.name){
    this.props.name = nextProps.name
  }
}

}

export default UploadFile
