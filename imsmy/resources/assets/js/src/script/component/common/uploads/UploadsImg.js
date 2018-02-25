import React, { Component } from 'react'
import { Upload, Icon, Modal, message, Layout,Button } from 'antd'
import Fetch from 'utils/fetch'
import TimeChange from 'utils/format'
const startUrl = 'http://img.ects.cdn.hivideo.com/'
const endHandle = '?imageMogr2/thumbnail/78x/gravity/Center/crop/78x78'
// const endHandle ='?imageMogr2/thumbnail/!20p/gravity/Center/crop/78x78'
class UploadsImg extends Component{
  constructor(props) {
    super(props)
    this.state = {
      previewVisible: false,
      previewImage: '',
      fileList: [],
      token:"",
      nowTimes:"",
    }

  }

  handleCancel = (fileList) => {
    const file = this.state.fileList[0]
    file.url = file.url +endHandle
    file.thumbUrl = file.thumbUrl+endHandle
    this.setState({
       previewVisible: false
     })
     // console.log(file,'guanbi');
  }

  handlePreview = (fileList) => {
    const file = this.state.fileList[0]
    // console.log(file,'xianshi');
    if(file.url){
      file.url = file.url.replace(endHandle,'')
    }
    if(file.thumbUrl){
      file.thumbUrl = file.thumbUrl.replace(endHandle,'')
      // console.log(file.thumbUrl,'yulan');
    }
    this.setState({
      previewImage: file.url || file.thumbUrl,
      previewVisible: true,
    });
  }

  data=(file)=>{
    const { fileList } =this.state
    this.setState({
      nowTimes:TimeChange.NowTime()
    })
    const suffix ="." + file.name.replace(/^.+\./,'')
    let img_url = window.URL.createObjectURL(file)
    // file.thumbUrl = img_url
    let img = new Image()
    let that = this
    img.src = img_url
    img.onload=function(){
      const OriginalSize = "_"+img.width+"*"+img.height+"_"
      const key = that.props.name+that.state.nowTimes+OriginalSize+suffix
      let formData = new FormData()
      formData.append('file',file)
      formData.append('token',that.state.token)
      formData.append('key',key)
      Fetch.post({
        uri:"http://upload.qiniup.com",
        callback:(res)=>{
          console.log(res);
          if(res.key){
            file.url = startUrl + res.key + endHandle
            file.thumbUrl = startUrl + res.key + endHandle
            that.setState(({ fileList }) => ({
              fileList: [...fileList, file],
            }))
            that.props.onChange(res.key)
          }
        },
        formData:formData
      })
    }

    // console.log(fileList,'fd');
  }
  handleUpload=(file)=>{
    return false
  }

  handleDelete=(file) => {
    if(this.props.onChange){
      this.props.onChange('')
    }
    this.setState(({ fileList }) => {
      const index = fileList.indexOf(file);
      const newFileList = fileList.slice();
      newFileList.splice(index, 1);
      return {
        fileList: newFileList,
      };
    });
  }
  handleChangeFileList=(value)=>{
    var obj = value
    const newObj = obj.replace(/http:(\S*).com[^\s]/,'')
    if(newObj !== '' && this.state.fileList.length === 0){
      const file ={
       uid: 1,
       status: 'done',
       url:startUrl+newObj+endHandle,
       thumbUrl:startUrl+newObj+endHandle
      }
      this.setState(({ fileList }) => ({
        fileList: [...fileList, file],
      }))
    }
  }



  render(){
    const { previewImage, previewVisible, fileList } = this.state;
    return (
      <div className="clearfix">
        <Upload
          action="http://upload.qiniup.com"
          listType="picture-card"
          fileList={this.state.fileList}
          onPreview={this.handlePreview}
          // onChange={this.handleChange}
          beforeUpload={this.props.beforeUpload}
          customRequest={this.handleUpload}
          data={this.data}
          onRemove={this.handleDelete}
        >
          {this.state.fileList.length >= 1 ? null : this.props.uploadButton}
        </Upload>

        <Modal visible={previewVisible} footer={null} onCancel={this.handleCancel}>
          <img alt="example" style={{ width: '100%' }} src={previewImage} />
        </Modal>
      </div>
    )
  }
  componentDidMount(){
    let formData = new FormData()
    formData.append('type',"eimage")
    formData.append('location',"华东")
    Fetch.post({
      uri:'/api/admins/cloudStorage/token',
      callback:(res)=>{
        // console.log(res.token);
        this.setState({
          token:res.token
        })
      },
      formData:formData
    })
    if(this.props.showFile && this.props.showFile !== ''){
      this.handleChangeFileList(this.props.showFile)
    }
  }
  componentWillReceiveProps(nextPorps){
    // console.log(nextPorps);
    if(this.props.showFile && this.props.showFile !== ''){
      this.handleChangeFileList(this.props.showFile)
    }
    // if(this.props.cancelUp && this.props.cancelUp=== true){
    //   this.handleDelete()
    // }
  }

}

export default UploadsImg
