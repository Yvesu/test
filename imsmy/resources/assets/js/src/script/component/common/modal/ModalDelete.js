import React, { Component } from 'react'
import { Modal, Button, message } from 'antd'

const confirm = Modal.confirm
import Fetch from 'utils/fetch'

class ModalDelete extends Component {
	constructor(props){
    super(props)
     this.state = {
  	}
  }

  showDeleteConfirm=()=>{
      confirm({
        title: `${this.props.btnName}`,
        content:(<div>
					{this.props.id.length !==undefined? (<p>{`确定要删除这${this.props.id.length}条视频吗?`}</p>) :
					(
	          <div className="confirm_content_box">
	            <div className="confirm_img_box">
	              <img src={`${this.props.deleteImg}?imageMogr2/thumbnail/100x/gravity/Center/crop/89x50`} />
	            </div>
	            <p>{this.props.deleteDes!==''? this.props.deleteDes : (<b>无</b>)}</p>
	          </div>
	        )
				}
				</div>) ,
        okText: '确定',
        cancelText: '取消',
        onOk:()=>{
					if(this.props.deleteUri !== ''){
						this.handleChangeDelete()
					}else{
						message.error('地址不能为空')
					}
        },
        onCancel:()=>{
          message.success('取消删除');
        }
      })

  }
	handleChangeDelete=()=>{
		let formData=new FormData()
		if(this.props.id.length !==undefined){
			const IdLength = this.props.id.length
			if(IdLength ===1){
				const newId = this.props.id.toString()
				formData.append('id',newId)
			}else if(IdLength >1){
				const newId = this.props.id.join('|')
				 formData.append('id',newId)
			}
		}else{
			formData.append('id',this.props.id)
		}
		Fetch.post({
	 	 uri:this.props.deleteUri,
	 	 callback:(res)=>{
	 		 console.log(res,'shanchu');
	 		 if(res.message && res.message==='删除成功'){
	 			 message.success(res.message)
	 			 if(this.props.RefreshTableState){
	 				 this.props.RefreshTableState(true)
	 			 }
	 		 }else{
	 			 console.log(res,'删除错误');
	 			 message.error(res.message || res.error);
	 			 if(this.props.RefreshTableState){
	 				 this.props.RefreshTableState(true)
	 			 }
	 		 }
	 	 },
	 	 formData:formData
	  })
	}

  render() {
    return (
			<div>
       <Button style={{color:"#ff0000"}} onClick={this.showDeleteConfirm} >
       		{this.props.btnName}
      	</Button>
			</div>
    );
  }
}


export default ModalDelete
