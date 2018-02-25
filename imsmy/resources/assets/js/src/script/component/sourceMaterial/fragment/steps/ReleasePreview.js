import React, { Component } from 'react'
import { Icon } from 'antd'
import Fetch from 'utils/fetch'
class ReleasePreview extends Component {
  constructor(props){
    super(props)
    this.state={
      data:{},
      playIcon:true
    }
  }
  handleChangeVideo=()=>{
    const onControls = this.refs.previewVideo
    if(onControls.paused){
      this.setState({
        playIcon:false
      })
      onControls.play()
    }else{
      onControls.pause()
      this.setState({
        playIcon:true
      })
    }
  }
  render(){
    return(
      <div style={{paddingLeft:70,paddingTop:17}}>
        <div className="upload_publish_img">
          <img src={`${this.state.data.cover}?imageMogr2/thumbnail/!20p/gravity/Center/crop/160x90`}
          alt="封面图片" />
        </div>
        <div className="upload_publish_content">
          <div>
            <p>
              <span>片段描述：</span>
              <span>{this.state.data.description}</span>
            </p>
            <p>
              <span>画面比例：</span>
              <span>{this.state.data.aspect_radio}</span>
            </p>
            <p >
              <span>时长：</span>
              <span>{this.state.data.duration} </span>
            </p>
            <p>
              <span>下载资费：</span>
              <span>{this.state.data.integral}</span>
            </p>
            <p>
              <span>拍摄地址：</span>
              <span>{this.state.data.address}</span>
            </p>

            <p>
              <span>标签：</span>
              <span>{this.state.data.label}</span>
            </p>
          </div>

          <div className="video_play_box">
            <p>演示:</p>
            <div className="push_videos_box">
              <video src={this.state.data.play} ref="previewVideo"
              ></video>
              <div className="video_play_box_mask" onClick={this.handleChangeVideo}>
                {/* <img src="" alt="播放"/> */}
                <Icon type={this.state.playIcon===true?"caret-right" : "pause" } />
                {/* <Icon type="pause" /> */}
              </div>
            </div>
            <p>片段素材包：<span>{this.state.data.size}</span></p>
          </div>
        </div>
      </div>
    )
  }
  componentDidMount(){
    console.log(this.props);
    this.setState({
      data:this.props.nowData
    })
  }
}

export default ReleasePreview
