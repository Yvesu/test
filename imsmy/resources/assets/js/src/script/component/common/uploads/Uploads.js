import React, { Component } from 'react'
import { Upload, Icon, Modal, message } from 'antd'
import Fetch from 'utils/fetch'
import TimeChange from 'utils/format'
const startUrl = 'http://video.ects.cdn.hivideo.com/'

class Uploads extends Component{
  constructor(props){
    super(props)
    this.state = {
      previewVisible: false,
      previewImage: '',
      fileList: [],
      name:this.props.name,
      nowTimes:""
    }
  }

  handleCancel = () => {
    this.setState({ previewVisible: false })
    // console.log(this.refs.videoPlay.play,'ss');
    if(this.refs.videoPlay && !this.refs.videoPlay.paused){
      this.refs.videoPlay.pause()
    }
  }

  handlePreview = (file) => {
    // console.log(file);
    // console.log(this.refs.videoPlay);
    this.setState({
      previewImage: file.url || file.thumbUrl,
      previewVisible: true,
    })
    if(this.refs.videoPlay){
      this.refs.videoPlay.play()
    }
  }

  handleChange = ({ fileList }) =>{
    if(fileList[0].response && fileList[0].response != undefined){
      this.props.onChange(fileList[0].response.key)
      if(this.props.fileSize){
        this.props.fileSize(fileList[0].size)
      }
    }
    this.setState({ fileList })
  }
  data=(file)=>{
    const suffix ="." + file.name.replace(/^.+\./,'')
    this.setState({
      nowTimes:TimeChange.NowTime()
    })
    return {
      token:this.state.token,
      key:this.props.name+this.state.nowTimes+suffix
    }
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
       name:'视频文件',
       url:startUrl+newObj,
      }
      this.setState(({ fileList }) => ({
        fileList: [...fileList, file],
      }))
    }
  }

  render(){
    // console.log(this.state.fileList,'ceshi');
    const { previewVisible, previewImage, fileList } = this.state

    return(
      <div className="clearfix">
        <Upload
          action="http://upload.qiniup.com"
          listType="picture-card"
          fileList={fileList}
          onPreview={this.handlePreview}
          onChange={this.handleChange}
          beforeUpload={this.props.beforeUpload}
          data={this.data}
          onRemove={this.handleDelete}
        >
          {fileList.length >= 1 ? null : this.props.uploadButton}
        </Upload>

        <Modal visible={previewVisible} footer={null} onCancel={this.handleCancel}>
          <video alt="example" style={{ width: '100%' }} src={previewImage} autoPlay
            ref="videoPlay"
          ></video>
        </Modal>
      </div>
    )
  }
  componentDidMount(){
    let formData = new FormData()
    formData.append('type',"evideo")
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
    if(this.props.showFile && this.props.showFile !== ''){
      this.handleChangeFileList(this.props.showFile)
    }
    // if(this.props.cancelUp && this.props.cancelUp=== true){
    //   this.handleDelete()
    // }
  }
}

export default Uploads
