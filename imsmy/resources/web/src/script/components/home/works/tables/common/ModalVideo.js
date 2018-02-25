import React, { Component } from 'react'
import { Modal, Button } from 'antd'

class ModalVideo extends Component{
  constructor(props){
    super(props)
    this.state={
       visible: false
    }
  }
showModal = (e) => {
  this.setState({
    visible: true,
  });
  const onControls = this.refs.modalVideo
  if(onControls!==undefined){
    this.handleChangeVideo()
  }

}
// handleOk = (e) => {
//   console.log(e);
//   this.setState({
//     visible: false,
//   });
// }
handleCancel = (e) => {
  this.setState({
    visible: false,
  });
  this.handleChangeVideo()
}
handleChangeVideo=()=>{
  const onControls = this.refs.modalVideo
  if(onControls.paused){
    onControls.play()
  }else{
    onControls.pause()
  }
}

  render(){
    return(
      <div className="tables_opus_cover_duration">
        <p onClick={this.showModal}>
          <img src={this.props.cover} alt=""/>
          <span className="tables_text">{this.props.duration}</span>
        </p>
       <Modal
         title="视频播放"
         visible={this.state.visible}
         onChange={this.test}
         // onOk={this.handleOk}
         onCancel={this.handleCancel}
         footer={null}
       >
         <p style={{width:'100%',height:"100%"}}>
           <video src={this.props.videoUrl} autoPlay style={{width:'100%',height:"100%"}} ref="modalVideo"></video>
         </p>
       </Modal>
      </div>
    )
  }

}

export default ModalVideo
