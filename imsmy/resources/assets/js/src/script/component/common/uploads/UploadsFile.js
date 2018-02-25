import React, { Component } from 'react'
import { Upload, Icon, Modal, message } from 'antd'
import Fetch from 'utils/fetch'
import TimeChange from 'utils/format'
const startUrl = 'http://file.ects.cdn.hivideo.com/'

class UploadsFile extends Component{
  constructor(props){
    super(props)
    this.state = {
      previewVisible: false,
      previewImage: '',
      fileList: [],
      name:this.props.name,
      nowTimes:''
    }
    // console.log(this.props.fileSize,'445');
  }

  handleCancel = () => {
    this.setState({ previewVisible: false })
    // console.log(this.refs.videoPlay.play,'ss');
    // if(this.refs.videoPlay && !this.refs.videoPlay.paused){
    //   this.refs.videoPlay.pause()
    // }
  }

  handlePreview = (file) => {
    // console.log(file);
    // console.log(this.refs.videoPlay);
    this.setState({
      previewImage: file.url || file.thumbUrl,
      previewVisible: true,
    })
    // if(this.refs.videoPlay){
    //   this.refs.videoPlay.play()
    // }
  }

  handleChange = ({ fileList}) =>{
    if(fileList[0].response && fileList[0].response != undefined){
      console.log(fileList[0].response);
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
    const key = this.props.name+this.state.nowTimes+suffix
    return{
      token:this.state.token,
      key:key
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
       name:'压缩文件',
       url:startUrl + newObj,
      }
      this.setState(({ fileList }) => ({
        fileList: [...fileList, file],
      }))
    }
  }

  render(){
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
          <img alt="example" style={{ width: '100%' }} src={previewImage} />
        </Modal>
      </div>
    )
  }
  componentDidMount(){
    let formData = new FormData()
    formData.append('type',"efile")
    formData.append('location',"华东")
    Fetch.post({
      uri:'/api/admins/cloudStorage/token',
      callback:(res)=>{
        // console.log(res);
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
    //   this.handleDelete("")
    //   console.log(1);
    // }
  }
}

export default UploadsFile
