import React, { Component } from 'react'
import { Button, Icon, Modal } from 'antd'
const ButtonGroup=Button.Group

import ModalTransfer from '../modal/ModalTransfer'
import ModalScreen from '../modal/ModalScreen'
import ModalDelete from '../modal/ModalDelete'

//按钮
class DetailBtn extends Component{
  constructor(props){
    super(props)
    this.state={
      visible:false
    }
    // console.log(this.props);
  }

  GoToNextDetail(){
    //点击跳转下一个

    this.props.GoToNextDetail()
  }
  GoToPrevDetail(){
    // 上一个
    this.props.GoToPrevDetail()
  }
  //待定和解除还没有样式 所以自己先写一个
  render(){
    const BtnPopType=this.props.type
    return(
        <div className="detail_btn_box">
          <ButtonGroup>
            <Button disabled={this.props.PrevDisabled} onClick={this.GoToPrevDetail.bind(this)} >
                <Icon type="verticle-right" />
            </Button>
            <Button disabled={this.props.NextDisabled} onClick={this.GoToNextDetail.bind(this)} >
                  <Icon type="verticle-left" />
            </Button>
          </ButtonGroup>

          <div className="detail_btn_right">
            <Button style={{display:`${(BtnPopType=='videoScreen')? "none" : "block"}`}}>
                <ModalTransfer id={this.props.id} visible={this.state.visible}/>{" | "}
            </Button>

            <Button style={{display:`${(BtnPopType=='videoCensor')? "block" : "none"}`}}>
                <div><Button>待定</Button></div>{" | "}
            </Button>

            <Button style={{display:`${(BtnPopType=='videoScreen')? "none" : "block"}`}}>
                <ModalScreen id={this.props.id} visible={this.state.visible} />
            </Button>

            <Button style={{display:`${(BtnPopType=='videoScreen')? "block" : "none"}`}}>
                <div><Button>解除</Button></div>{" | "}
            </Button>

            <Button style={{display:`${(BtnPopType=='videoScreen')? "block" : "none"}`}}>
              <ModalDelete id={this.props.id} shotImg={this.props.shotImg} />
            </Button>
          </div>
        </div>
    )
  }
}

export default DetailBtn
