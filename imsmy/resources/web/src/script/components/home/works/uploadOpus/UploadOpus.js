import React, { Component } from 'react'
import { Checkbox } from 'antd'
import UploadFile from '../../common/upload/UploadFile'
import UploadOpusForm from './form/UploadOpusForm'

class UploadOpus extends Component{
  constructor(props){
    super(props)
    this.state={
      autoUpSubmit:true,
      isAuto:false,
      fileKey:'',
      uploadFileSize:'',
      userId:'',
      upOpusId:''
    }
  }
  handleGetUploadFileKey=(value)=>{
    this.setState({
      fileKey:value
    })
  }
  handleChangeUploadFileSize=(value)=>{
    this.setState({
      uploadFileSize:value
    })
    // console.log(this.state.uploadFileSize,'sss');
  }
  handleChangeAutoValue=(e)=>{
    console.log(e);
    this.setState({
      isAuto:e.target.checked
    })
  }
  handleChangeAutoUpSubmitDis=(value)=>{
    this.setState({
      autoUpSubmit:value
    })
  }
  handleGetUploadUserId=(value)=>{
      this.setState({
        userId:value
      })
  }
  handleGetUploadFileId=(value)=>{
      this.setState({
        upOpusId:value
      })
  }

  render(){
    // console.log(hashHistory);
    return(
      <div className='upload_opus_form'>
        <div className='breadcrumb_link'>
          <span>当前位置 </span>
          <span class="ant-breadcrumb-separator">/</span>
          <span>
            <a href="#/opus">作品</a>
          </span>
          <span class="ant-breadcrumb-separator">/</span>
          <span>上传作品 </span>
        </div>
        <div className="opus_form_and_upload">
          <UploadFile name={`web/upload/opus/${this.state.userId}/${this.state.upOpusId}/`}
            getFileKey={this.handleGetUploadFileKey}
            getFileSize={this.handleChangeUploadFileSize}
            changeAutoSubmit={this.handleChangeAutoUpSubmitDis}
          />
          <Checkbox disabled={this.state.autoUpSubmit}
            onChange={this.handleChangeAutoValue}
            style={{fontSize:12,margin:"5px 0 15px"}}
            >
            上传完成后自动提交
          </Checkbox>
          <UploadOpusForm
             isAuto={this.state.isAuto}
             fileKey={this.state.fileKey}
             fileSize={this.state.uploadFileSize}
             getUserId={this.handleGetUploadUserId}
             getFileId={this.handleGetUploadFileId}
           />
        </div>
      </div>
    )
  }
}

export default UploadOpus
