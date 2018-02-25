import React, { Component } from 'react'
import { Modal, Button } from 'antd'

const confirm = Modal.confirm


class ModalDelete extends Component {
	constructor(props){
    super(props)
     this.state = {
  	}
  }

  showDeleteConfirm(){
    if(this.props.onlyDelete === false){
      console.log(`数组${this.props.id}`);
      confirm({
        title: '批量删除',
        content: <p>{`确定要删除这${this.props.id.length}条视频吗?`}</p>,
        okText: '确定',
        cancelText: '取消',
        onOk() {
          console.log('OK');
        },
        onCancel() {
          console.log('取消删除');
        }
      })
    }else{
      console.log(`单个${this.props.id}`);
      confirm({
        title: '确定要删除这条视频吗?',
        content: (
          <div className="confirm_content_box">
            <div className="confirm_img_box">
              <img src={`${this.props.shotImg}?imageMogr2/thumbnail/90x`} />
            </div>
            <p>视频的描述，在视频详情页就是 数据里边的内容</p>
          </div>
        ),
        okText: '确定',
        cancelText: '取消',
        onOk() {
          console.log('删除成功');
          // console.log(this.props);
        },
        onCancel() {
          console.log('取消删除');
        }
      })
    }
  }


  render() {
    return (
			<div>
       <Button style={{color:"#ff0000"}} onClick={this.showDeleteConfirm.bind(this)} >
       		删除
      	</Button>
			</div>
    );
  }
}


export default ModalDelete
